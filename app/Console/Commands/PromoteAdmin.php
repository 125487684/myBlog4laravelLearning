<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PromoteAdmin extends Command
{
    public $signature = 'admin:promote {email} {--force : Skip the confirmation prompt}';

    protected $description = 'Promote a registered user to administrator';

    public function handle(): int
    {
        $users = User::whereRaw('LOWER(email) = LOWER(?)', [trim($this->argument('email'))])
            ->orderBy('id')
            ->get();

        if ($users->count() > 1) {
            $this->error('Multiple accounts match this email (differing only by case); refusing to guess.');

            return self::FAILURE;
        }

        $user = $users->first();

        if (! $user) {
            $this->error("No user found for email: {$this->argument('email')}");

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->info("User {$user->name} ({$user->email}) is already an admin, nothing to do.");

            return self::SUCCESS;
        }

        if (! $user->hasVerifiedEmail()) {
            $this->error("User {$user->email} has not verified their email; refusing to promote an unverified account.");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Promote {$user->name} ({$user->email}) to administrator?")) {
            $this->info('Aborted, no changes made.');

            return self::FAILURE;
        }

        $user->is_admin = true;
        $user->save();

        Log::info('User promoted to admin via admin:promote command.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'forced' => (bool) $this->option('force'),
        ]);

        $this->info("User {$user->name} ({$user->email}) has been promoted to admin.");

        return self::SUCCESS;
    }
}
