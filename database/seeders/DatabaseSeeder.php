<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /** Creates the manager account (idempotent). Riders come from `php artisan drezwel:import-sheet` or the Riders page. */
    public function run(): void
    {
        if (User::where('role', 'manager')->exists()) {
            $this->command->info('A manager account already exists.');

            return;
        }

        $password = Str::password(12, symbols: false);
        User::create(['name' => 'Drezwel Manager', 'email' => 'manager@drezwel.test', 'password' => $password, 'role' => 'manager', 'is_active' => true]);

        $this->command->warn("Manager login: manager@drezwel.test / {$password}  (shown once - change it)");
    }
}
