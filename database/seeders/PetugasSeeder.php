<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'petugas@sipus-del.test'],
            [
                'name' => 'Petugas Perpustakaan',
                'password' => Hash::make('PetugasDel123!'),
            ]
        );
    }
}
