<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage: php artisan admin:reset-password email [password]
     *
     * @var string
     */
    protected $signature = 'admin:reset-password {email} {password=secret}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password for an admin user (temporary debug command)';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password for {$email} updated to '{$password}'. Please delete this command after use.");
        return 0;
    }
}
