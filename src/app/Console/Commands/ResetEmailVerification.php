<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetEmailVerification extends Command
{
    protected $signature = 'user:reset-verify {userId}';
    protected $description = 'Reset email verification status for a user';

    public function handle()
    {
        $user = User::find($this->argument('userId'));

        if (! $user) {
            $this->error('User not found');
            return Command::FAILURE;
        }

        $user->email_verified_at = null;
        $user->save();

        $this->info("User {$user->id} email verification reset.");
        return Command::SUCCESS;
    }
}