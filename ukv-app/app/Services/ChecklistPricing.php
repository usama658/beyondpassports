<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Destination;

/**
 * Pricing for the standalone instant document-checklist product.
 *
 * Reuses the per-destination service-fee table (PricingService::tiers) so the checklist
 * charges the same per-destination tier price /apply uses, but only the SERVICE FEE
 * (no government fee — a checklist is information, not a submission).
 */
final class ChecklistPricing
{
    /** Valid tier keys for the checklist product. */
    public const TIERS = ['standard', 'express', 'premium'];

    public function __construct(private readonly PricingService $pricing) {}

    /** Service fee (GBP) for a destination + tier. Throws on an unknown/unpriced tier. */
    public function priceFor(Destination $destination, string $tier): float
    {
        if (! in_array($tier, self::TIERS, true)) {
            throw new \InvalidArgumentException("Unknown checklist tier '{$tier}'.");
        }

        $tiers = $this->pricing->tiers($destination);
        if (! isset($tiers[$tier])) {
            throw new \InvalidArgumentException(
                "Tier '{$tier}' is not priced for destination {$destination->getKey()}."
            );
        }

        return (float) $tiers[$tier]['service_fee'];
    }

    /**
     * Tier => price map for the gate cards (only positively-priced tiers).
     *
     * @return array<string,float>
     */
    public function cards(Destination $destination): array
    {
        $out = [];
        foreach ($this->pricing->tiers($destination) as $key => $row) {
            $fee = (float) $row['service_fee'];
            if ($fee > 0) {
                $out[$key] = $fee;
            }
        }

        return $out;
    }
}
