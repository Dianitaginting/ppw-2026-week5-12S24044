<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Buku;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            'Sistem Informasi', 
            'Pemrograman Web', 
            'Kecerdasan Buatan', 
            'Basis Data', 
            'Jaringan Komputer'
        ];

        foreach ($kategoris as $index => $nama) {
            $ktg = Kategori::create([
                'kode_kategori' => 'KTG0' . ($index + 1),
                'nama_kategori' => $nama,
            ]);

            Buku::factory(4)->create(['kategori_id' => $ktg->id]);
        }
    }
}