<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriBasisData = Kategori::firstOrCreate(
            ['kode_kategori' => 'KTG04'],
            ['nama_kategori' => 'Basis Data']
        );

        $kategoriPemrograman = Kategori::firstOrCreate(
            ['kode_kategori' => 'KTG02'],
            ['nama_kategori' => 'Pemrograman Web']
        );

        $bukuStokHabis = Buku::firstOrCreate(
            ['isbn' => '9786020000001'],
            [
                'judul' => 'Dasar-Dasar Basis Data',
                'penulis' => 'Tim SIPUS-Del',
                'penerbit' => 'Penerbit Kampus',
                'tahun_terbit' => 2026,
                'kategori_id' => $kategoriBasisData->id,
                'stok' => 0,
                'sinopsis' => 'Contoh buku dengan stok habis untuk uji peminjaman.',
            ]
        );

        $bukuStokSatu = Buku::firstOrCreate(
            ['isbn' => '9786020000002'],
            [
                'judul' => 'Laravel untuk Perpustakaan',
                'penulis' => 'Tim SIPUS-Del',
                'penerbit' => 'Penerbit Kampus',
                'tahun_terbit' => 2026,
                'kategori_id' => $kategoriPemrograman->id,
                'stok' => 1,
                'sinopsis' => 'Contoh buku dengan satu eksemplar tersedia.',
            ]
        );

        Peminjaman::firstOrCreate(
            ['kode_pinjam' => 'PJM-SAMPLE-001'],
            [
                'buku_id' => $bukuStokHabis->id,
                'nim_peminjam' => '12S24001',
                'nama_peminjam' => 'Mahasiswa Contoh Aktif',
                'tanggal_pinjam' => today()->subDays(2),
                'tanggal_jatuh_tempo' => today()->addDays(5),
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
            ]
        );

        Peminjaman::firstOrCreate(
            ['kode_pinjam' => 'PJM-SAMPLE-002'],
            [
                'buku_id' => $bukuStokSatu->id,
                'nim_peminjam' => '12S24002',
                'nama_peminjam' => 'Mahasiswa Contoh Selesai',
                'tanggal_pinjam' => today()->subDays(8),
                'tanggal_jatuh_tempo' => today()->subDay(),
                'tanggal_kembali' => today()->subDay(),
                'status' => 'dikembalikan',
            ]
        );
    }
}
