<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;

#[Signature('dashboard:make-admin {email} {--name= : Display name (default: the part before @)}')]
#[Description('Create (or promote) a dashboard admin who can log in and review questions')]
class MakeAdmin extends Command
{
    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->components->error('That is not a valid e-mail address.');

            return self::FAILURE;
        }

        $user = User::firstWhere('email', $email);

        if ($user !== null) {
            $user->update(['is_admin' => true]);
            $this->components->info("{$email} is now an admin.");

            return self::SUCCESS;
        }

        User::create([
            'name' => $this->option('name') ?: strstr($email, '@', true),
            'email' => $email,
            'password' => password('Password', required: true, validate: fn (string $value) => strlen($value) < 10 ? 'Use at least 10 characters.' : null),
            'is_admin' => true,
        ]);

        $this->components->info("Admin {$email} created.");

        return self::SUCCESS;
    }
}
