<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleAccountSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Create one verified sample account per role.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('Sample accounts are not created in production.');

            return;
        }

        foreach (Role::cases() as $role) {
            $password = $this->passwordFor($role);

            $user = User::updateOrCreate(
                ['email' => $role->value.'@icsmi.test'],
                ['name' => 'Sample '.$role->label(), 'password' => $password],
            );
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole($role);

            $this->command->line($user->email.'  '.$password);
        }
    }

    /**
     * Use the fixed password from config when set, otherwise a random one.
     */
    private function passwordFor(Role $role): string
    {
        $configured = config('seeding.passwords.'.$role->value);

        return filled($configured) ? $configured : Str::password(20);
    }
}
