<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'soft7art@dailyops.com');
        $password = env('ADMIN_PASSWORD');

        $admin = User::firstOrNew(['email' => $email]);

        if (! $admin->exists && blank($password) && app()->isProduction()) {
            throw new RuntimeException('ADMIN_PASSWORD must be set before seeding the first production admin user.');
        }

        $admin->name = env('ADMIN_NAME', 'DailyOps Admin');
        $admin->role = 'admin';

        if (! $admin->exists || filled($password)) {
            $admin->password = Hash::make($password ?: Str::password(32));
        }

        $admin->save();
    }
}
