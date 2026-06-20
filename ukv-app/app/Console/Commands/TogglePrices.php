<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * One-command toggle for marketing-surface pricing.
 *
 *   php artisan ukv:prices off    # hide service-fee prices on cards/header/money tiers + Offer schema
 *   php artisan ukv:prices on     # show them again
 *   php artisan ukv:prices        # report current state
 *
 * Rewrites UKV_SHOW_PRICES in .env and rebuilds the config cache so the change is live
 * immediately. The apply-step price and order receipt are never affected.
 */
final class TogglePrices extends Command
{
    protected $signature = 'ukv:prices {state? : on|off (omit to show current state)}';

    protected $description = 'Show or hide service-fee prices on marketing surfaces';

    public function handle(): int
    {
        $state = $this->argument('state');

        if ($state === null) {
            $on = (bool) config('ukv.show_prices');
            $this->info('Marketing prices are currently '.($on ? 'ON (shown)' : 'OFF (hidden)').'.');
            $this->line('Flip with: php artisan ukv:prices '.($on ? 'off' : 'on'));

            return self::SUCCESS;
        }

        $state = strtolower((string) $state);
        $truthy = ['on', 'true', '1', 'yes', 'show'];
        $falsy = ['off', 'false', '0', 'no', 'hide'];

        if (! in_array($state, [...$truthy, ...$falsy], true)) {
            $this->error('Invalid state "'.$state.'". Use: on | off');

            return self::INVALID;
        }

        $value = in_array($state, $truthy, true) ? 'true' : 'false';

        if (! $this->writeEnv('UKV_SHOW_PRICES', $value)) {
            $this->error('Could not write to .env — set UKV_SHOW_PRICES='.$value.' manually.');

            return self::FAILURE;
        }

        // Rebuild config cache so the new value is live (no-op safe if config wasn't cached).
        Artisan::call('config:clear');
        if (app()->environment('production')) {
            Artisan::call('config:cache');
        }

        $this->info('Marketing prices are now '.($value === 'true' ? 'ON (shown)' : 'OFF (hidden)').'.');
        $this->line('Apply-step price and order receipt are unaffected.');

        return self::SUCCESS;
    }

    /**
     * Set KEY=value in .env, updating the existing line or appending a new one.
     */
    private function writeEnv(string $key, string $value): bool
    {
        $path = base_path('.env');
        if (! is_file($path) || ! is_writable($path)) {
            return false;
        }

        $contents = (string) file_get_contents($path);
        $line = $key.'='.$value;

        if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $contents) === 1) {
            $contents = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $contents);
        } else {
            $contents = rtrim($contents, "\r\n")."\n".$line."\n";
        }

        return file_put_contents($path, $contents) !== false;
    }
}
