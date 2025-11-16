<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'vendor']);
        Role::firstOrCreate(['name' => 'customer']);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@email.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $vendor = User::factory()->create([
            'name' => 'Vendor User',
            'email' => 'vendor@email.com',
            'password' => bcrypt('password'),
        ]);
        $vendor->assignRole('vendor');

        $customer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@email.com',
            'password' => bcrypt('password'),
        ]);
        $customer->assignRole('customer');
    }
}
