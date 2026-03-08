<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            'angala.k@devlapan.com', // Katrina Angala
            // 'mapalo.n@devlapan.com', // excluded
            'melvino.g@devlapan.com', // Melvino Gawanab
            'morais.p@devlapan.com', // Morais I
            'mutelo.m@devlapan.com', // Mutelo M
            'muyoba.b@devlapan.com', // Muyoba B
            'shingo.n@devlapan.com', // Anna S
            'siboli.n@devlapan.com', // Siboli N
            'sisamu.m@devlapan.com', // Sisamu M
            'mafenyeho.l@devlapan.com' //Mafenyeho L
        ];

        foreach ($users as $email) {
            User::create([
                'email' => $email,
                'password' => Hash::make(str_replace('.com', '', $email)), 
                'must_change_password' => true,
                'status' => 'active',
                'failed_attempts' => 0,
                'employee_id' => rand(1000, 9999),
            ]);
        }
    }
}