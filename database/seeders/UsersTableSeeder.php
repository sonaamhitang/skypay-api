<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert Superadmin
        DB::table('users')->insert([
            'id' => Str::uuid(),
            'name' => 'Super Admin',
            'email' => 'sa@test.test',
            'phone' => '1234567890',
            'password' => Hash::make('12345678'), // You should probably use a stronger password ;)
            'status' => 'active',
            'subscription_plan' => 'platinum',
            'balance' => 10000.00,
            'email_verified_at' => now(),
            'last_login_at' => now(),
            'last_login_ip' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
            // 'parent_id' => null, // Since this is a top-level user, no parent_id is needed.
        ]);

        // Insert Admin
        DB::table('users')->insert([
            'id' => Str::uuid(),
            'name' => 'Admin User',
            'email' => 'admin@test.test',
            'phone' => '0987654321',
            'password' => Hash::make('12345678'), // Again, choose a better password
            'status' => 'active',
            'subscription_plan' => 'gold',
            'balance' => 5000.00,
            'email_verified_at' => now(),
            'last_login_at' => now(),
            'last_login_ip' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
            // 'parent_id' => [UUID of the Super Admin], if you want to establish a hierarchy.
        ]);

        // Insert some users
        for ($i = 1; $i <= 10; $i++) {
            DB::table('users')->insert([
                'id' => "00" . $i,
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@test.test',
                'phone' => '000' . $i . '000000',
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'subscription_plan' => 'free',
                'balance' => 0.00,
                'email_verified_at' => now(),
                'last_login_at' => now(),
                'last_login_ip' => '127.0.0.1',
                'created_at' => now(),
                'updated_at' => now(),
                // 'parent_id' => [UUID of the Admin or Super Admin], to place under a hierarchy.
            ]);
        }
    }
}
