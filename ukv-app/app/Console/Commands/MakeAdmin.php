<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Validator;

/**
 * Create or repair a Filament admin login. Idempotent: run it as many times as
 * you like — it never duplicates a user, only fixes the row for the given email.
 *
 * Fixes the four things that block /admin login:
 *   1. user row missing            -> creates it
 *   2. forgotten / wrong password  -> resets the hash (prompted, hidden input)
 *   3. role not a back-office role -> forces role = admin (canAccessPanel gate)
 *   4. stale 2FA lockout           -> clears two_factor_* so you are not asked for a code
 *
 * Password is prompted with hidden input (never in shell history / process list),
 * or pass --password= for an unattended reset. Email is verified automatically.
 *
 *   php artisan app:make-admin admin@beyondpassports.co.uk
 *   php artisan app:make-admin admin@beyondpassports.co.uk --keep-2fa
 */
final class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin
        {email : The admin login email}
        {--name= : Display name (defaults to the part before @)}
        {--password= : Set non-interactively (otherwise prompted, hidden)}
        {--keep-2fa : Do not clear existing two-factor settings}';

    protected $description = 'Create or repair a Filament admin login (password, role, 2FA)';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Not a valid email: '.$email);

            return self::INVALID;
        }

        $password = (string) ($this->option('password') ?? '');
        if ($password === '') {
            $password = (string) $this->secret('New password (input hidden)');
            $confirm = (string) $this->secret('Confirm password');
            if ($password !== $confirm) {
                $this->error('Passwords did not match.');

                return self::FAILURE;
            }
        }

        $validator = Validator::make(['password' => $password], [
            'password' => ['required', PasswordRule::min(12)],
        ]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first('password'));

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        $name = (string) ($this->option('name') ?? '')
            ?: ($existing->name ?? ucfirst(explode('@', $email)[0]));

        $attributes = [
            'name' => $name,
            'password' => Hash::make($password),
            'role' => UserRole::Admin,
            'email_verified_at' => $existing->email_verified_at ?? now(),
        ];

        // Clear a stale 2FA lockout unless asked to keep it (columns exist per breezy migration).
        if (! $this->option('keep-2fa') && Schema::hasColumn('users', 'two_factor_secret')) {
            $attributes['two_factor_secret'] = null;
            $attributes['two_factor_recovery_codes'] = null;
            $attributes['two_factor_confirmed_at'] = null;
        }

        $user = User::updateOrCreate(['email' => $email], $attributes);

        $this->info(($existing ? 'Repaired' : 'Created').' admin: '.$user->email);
        $this->line('  role: '.$user->role->value.' (back-office role — passes the /admin access gate)');
        if (! $this->option('keep-2fa')) {
            $this->line('  two-factor: cleared (you can re-enable it in the panel profile)');
        }
        $this->line('Sign in at /admin with this email and the password you just set.');

        return self::SUCCESS;
    }
}
