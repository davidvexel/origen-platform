<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreatePanelUser extends Command
{
    protected $signature = 'panel-user:create
        {name : Full name}
        {email : Login email}
        {--role=admin : admin or cashier}
        {--generate-password : Generate and display a temporary password for non-interactive environments}';

    protected $description = 'Create an administrator or cashier for the Filament panel';

    public function handle(): int
    {
        $role = (string) $this->option('role');

        if (! in_array($role, ['admin', 'cashier'], true)) {
            $this->error('Role must be admin or cashier.');

            return self::FAILURE;
        }

        $generatedPassword = (bool) $this->option('generate-password');
        $password = $generatedPassword
            ? Str::password(20)
            : (string) $this->secret('Password (minimum 8 characters)');
        $confirmation = $generatedPassword
            ? $password
            : (string) $this->secret('Confirm password');
        $data = [
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => $password,
            'password_confirmation' => $confirmation,
            'role' => $role,
        ];
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:admin,cashier'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => $role,
            'active' => true,
        ]);

        $this->info("Panel user created with role {$role}.");

        if ($generatedPassword) {
            $this->warn('Copy this temporary password now and change it after signing in:');
            $this->line($password);
        }

        return self::SUCCESS;
    }
}
