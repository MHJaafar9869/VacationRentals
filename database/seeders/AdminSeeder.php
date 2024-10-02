<?php

namespace Database\Seeders;

use App\Models\Owner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Owner::updateOrCreate(
            ['email' => 'admin@gmail.com'],  
            [
                'name' => 'Admin',              
                'password' => bcrypt('123456789'),
                'role' => 'admin',
            ]
        );
    }
}
