<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        for ($i = 7; $i <= 40; $i++) {
            DB::table('users')->insert([
                'name' => 'Traveler ' . $i,
                'email' => 'traveler' . $i . '@gmail.com',
                'password' => bcrypt('123123123'), // Make sure to hash the password
                'phone' => '01097329869', // or provide a default value
                'image' => null, // or provide a default value
                'address' => '11 Test St. Cairo, Egypt', // or provide a default value
                'gender' => 'Male', // default gender
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
