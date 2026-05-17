<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'slug' => 'admin',
                'description' => 'Admin is the highest role with full permissions to manage the system, including user management, product management, order management, and access to all reports.',
            ],
            [
                'slug' => 'customer',
                'description' => 'Customer is the role assigned to regular users who can browse products, place orders, and manage their own profiles. Customers have limited access compared to admins and cannot perform administrative tasks.',
            ],

        ];
        foreach ($roles as $role) {
            Role::updateOrCreate([
                'slug' => $role['slug'],
                'description' => $role['description'],
            ]);
        }
    }
}
