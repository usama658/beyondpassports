<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EligibilityLane;
use App\Enums\OrderBlocker;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Mail\OwnerDigestMail;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Owner daily digest (ported from ukv-owner-digest.php → ukv_owner_digest*).
 *
 * Composes the day's pending-actions digest for the business owner and emails it
 * to the configured owner address. The digest is three actionable buckets over
 * OPEN orders only (status not in OrderStatus::CLOSED):
 *
 *   1. Manual review awaiting clearance — eligibility lane manual_review/referred,
 *      i.e. the eligibility gate (OrderService §2) is blocking the order at `paid`.
 *   2. SLA-breached — created_at + tier-SLA window is in the past.
 *   3. Awaiting documents — status awaiting_docs OR blocker docs_missing.
 *
 * No-op behaviour:
 *   - If no owner email is configured, the command logs a notice and exits SUCCESS
 *     (a scheduled run must never fail just because the address is unset).
 *   - If nothing needs action, nothing is sent (unless --force is passed) and the
 *     command exits SUCCESS.
 *
 * Owner address resolution (no config-file edit required): config('ukv.owner_email')
 * if present, else env('UKV_OWNER_EMAIL'), else config('mail.from.address').
 */
class OwnerDigest extends Command
{
    /** @var string */
    protected $signature = 'ukv:owner-digest
        {--force : Send even when there are no pending actions}
        {--dry-run : Compose and print the digest without sending email}';

    /** @var string */
    protected $description = 'Email the business owner a daily digest of orders needing action';

    /** SLA window in hours per tier (matches WP ukv_order_sla_hours + OrderTier doc). */
    private const SLA_HOURS = [
        'premium' => 12,
        'express' => 24,
        'standard' => 72,
    ];

    private const SLA_DEFAULT_HOURS = 72;

    public function handle(): int
    {
        $owner = $this->ownerEmail();
        $dryRun = (bool) $this->option('dry-run');

        if ($owner === '' && ! $dryRun) {
            $this->warn('No owner email configured (config ukv.owner_email / env UKV_OWNER_EMAIL). Skipping.');

            return self::SUCCESS;
        }

        $digest = $this->buildDigest();
        $pending = (int) $digest['counts']['pending'];

        $this->line("Pending actions: {$pending} "
            ."(manual review {$digest['counts']['manual_review']}, "
            ."SLA breached {$digest['counts']['sla_breached']}, "
            ."awaiting docs {$digest['counts']['awaiting_docs']}).");

        if ($pending === 0 && ! $this->option('force')) {
            $this->info('Nothing needs action today — no digest sent.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            foreach ($digest['sections'] as $key => $rows) {
                $this->line("[{$key}] ".count($rows).' order(s)');
                foreach ($rows as $row) {
                    $this->line("  - {$row['ref']} · {$row['name']} · {$row['detail']}");
                }
            }
            $this->info('[dry-run] digest not sent.');

            return self::SUCCESS;
        }

        // Best-effort send; never let a mail-layer error fail a scheduled run.
        try {
            Mail::to($owner)->queue(new OwnerDigestMail($digest));
            $this->info("Owner digest queued to {$owner}.");
        } catch (\Throwable $e) {
            $this->error('Owner digest send failed: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Resolve the owner email from config, env, then the mail from-address.
     */
    private function ownerEmail(): string
    {
        $candidates = [
            config('ukv.owner_email'),
            env('UKV_OWNER_EMAIL'),
            config('mail.from.address'),
        ];

        foreach ($candidates as $value) {
            $value = trim((string) ($value ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * Build the digest payload: counts + the specific orders per actionable bucket.
     *
     * @return array{counts: array<string, int>, sections: array<string, array<int, array<string, string>>>, generated_at: string}
     */
    private function buildDigest(): array
    {
        $closed = array_map(static fn (OrderStatus $s): string => $s->value, OrderStatus::CLOSED);
        $now = Carbon::now();

        $open = static fn (Builder $q): Builder => $q->whereNotIn('status', $closed);

        // 1. Manual review awaiting clearance.
        $manualReview = Order::query()
            ->where($open)
            ->whereIn('eligibility', [EligibilityLane::ManualReview->value, EligibilityLane::Referred->value])
            ->with('destination')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $o): array => $this->row(
                $o,
                'eligibility: '.($o->eligibility instanceof EligibilityLane ? $o->eligibility->name : (string) $o->eligibility)
            ))
            ->all();

        // 2. SLA breached (computed per row — window varies by tier).
        $slaRows = [];
        Order::query()
            ->where($open)
            ->with('destination')
            ->orderByDesc('created_at')
            ->chunkById(500, function ($chunk) use (&$slaRows, $now): void {
                foreach ($chunk as $order) {
                    $hours = $this->slaHoursFor($order->tier);
                    if ($order->created_at !== null && $order->created_at->copy()->addHours($hours)->lt($now)) {
                        $overdueHours = (int) $order->created_at->copy()->addHours($hours)->diffInHours($now);
                        $slaRows[] = $this->row($order, "overdue ~{$overdueHours}h (".$this->tierLabel($order->tier).' SLA)');
                    }
                }
            });

        // 3. Awaiting documents (status awaiting_docs OR blocker docs_missing).
        $awaitingDocs = Order::query()
            ->where($open)
            ->where(function (Builder $q): void {
                $q->where('status', OrderStatus::AwaitingDocs->value)
                    ->orWhere('blocker', OrderBlocker::DocsMissing->value);
            })
            ->with('destination')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $o): array => $this->row(
                $o,
                $o->next_action !== null && $o->next_action !== '' ? $o->next_action : 'awaiting documents'
            ))
            ->all();

        $sections = [
            'manual_review' => $manualReview,
            'sla_breached' => $slaRows,
            'awaiting_docs' => $awaitingDocs,
        ];

        // Distinct orders needing action (an order can appear in more than one bucket).
        $distinct = [];
        foreach ($sections as $rows) {
            foreach ($rows as $row) {
                $distinct[$row['id']] = true;
            }
        }

        $counts = [
            'manual_review' => count($manualReview),
            'sla_breached' => count($slaRows),
            'awaiting_docs' => count($awaitingDocs),
            'pending' => count($distinct),
        ];

        return [
            'counts' => $counts,
            'sections' => $sections,
            'generated_at' => $now->format('D j M Y, H:i'),
        ];
    }

    /**
     * Flatten an order into the digest row shape consumed by the mail view.
     *
     * @return array{id: int, ref: string, name: string, destination: string, status: string, detail: string}
     */
    private function row(Order $order, string $detail): array
    {
        $status = $order->status instanceof OrderStatus ? $order->status->name : (string) $order->status;

        return [
            'id' => (int) $order->getKey(),
            'ref' => (string) ($order->order_ref ?? '#'.$order->getKey()),
            'name' => trim((string) ($order->name ?? '')) !== '' ? (string) $order->name : '(no name)',
            'destination' => trim((string) ($order->destination_name ?? '')) !== '' ? (string) $order->destination_name : '—',
            'status' => $status,
            'detail' => $detail,
        ];
    }

    private function slaHoursFor(OrderTier|string|null $tier): int
    {
        $key = $tier instanceof OrderTier ? $tier->value : (string) ($tier ?? '');

        return self::SLA_HOURS[$key] ?? self::SLA_DEFAULT_HOURS;
    }

    private function tierLabel(OrderTier|string|null $tier): string
    {
        if ($tier instanceof OrderTier) {
            return $tier->name;
        }
        $key = (string) ($tier ?? '');

        return $key !== '' ? ucfirst($key) : 'Standard';
    }
}
