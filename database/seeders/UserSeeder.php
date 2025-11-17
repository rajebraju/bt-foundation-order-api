<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create roles first
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'vendor']);
        Role::firstOrCreate(['name' => 'customer']);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@email.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $vendor = User::create([
            'name' => 'Vendor',
            'email' => 'vendor@email.com',
            'password' => Hash::make('password'),
        ]);
        $vendor->assignRole('vendor');

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@email.com',
            'password' => Hash::make('password'),
        ]);
        $customer->assignRole('customer');
    }
}
