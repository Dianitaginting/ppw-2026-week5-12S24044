<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat akun petugas perpustakaan
        $this->call(PetugasSeeder::class);

        // Daftar kategori buku
        $kategoris = [
            'Sistem Informasi',
            'Pemrograman Web',
            'Kecerdasan Buatan',
            'Basis Data',
            'Jaringan Komputer',
        ];

        foreach ($kategoris as $index => $nama) {
            // Membuat kategori hanya jika belum tersedia
            $ktg = Kategori::firstOrCreate(
                [
                    'kode_kategori' => 'KTG0' . ($index + 1),
                ],
                [
                    'nama_kategori' => $nama,
                ]
            );

            // Membuat empat buku untuk setiap kategori
            Buku::factory(4)->create([
                'kategori_id' => $ktg->id,
            ]);
        }
    }
}
