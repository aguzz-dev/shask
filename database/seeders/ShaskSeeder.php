<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ShaskSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'full_name' => 'Testing Shhask',
            'username' => 'shask_test',
            'email' => 'shask@mailnesia.com',
            'password' => Hash::make('desarrolloshask'),
            'coins' => 0,
            'avatar' => null,
            'status' => 0,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
