<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);
    }
}
