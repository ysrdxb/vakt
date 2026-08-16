<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperatorSeeder extends Seeder
{
    public function run(): void
    {
        // Create Yasir account (Operator)
        User::updateOrCreate(
            ['email' => 'yasir@kunnatta.is'],
            [
                'name'     => 'Yasir',
                'email'    => 'yasir@kunnatta.is',
                'password' => Hash::make('Yasir@123'),
                'role'     => 'operator',
            ]
        );

        // Create Hans account (Client)
        User::updateOrCreate(
            ['email' => 'hans@kunnatta.is'],
            [
                'name'     => 'Hans',
                'email'    => 'hans@kunnatta.is',
                'password' => Hash::make('Hans@123'),
                'role'     => 'client',
            ]
        );
    }
}
