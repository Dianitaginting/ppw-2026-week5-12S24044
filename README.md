# SIPUS-Del

SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del) adalah aplikasi web untuk mengelola data buku dan kategori perpustakaan. Project ini dibuat untuk Tugas Mandiri Praktikum Pemrograman dan Pengujian Aplikasi Web (PPW) Minggu 5 di Institut Teknologi Del.

## Teknologi

- Laravel 13
- PHP 8.5 (project mensyaratkan PHP `^8.3`)
- SQLite
- Bootstrap 5.3 dan Vite 5
- PHPUnit

## Fitur

- CRUD buku dan kategori dengan resource controller dan implicit route model binding.
- Relasi `Kategori hasMany Buku` dan `Buku belongsTo Kategori`.
- Validasi server-side, termasuk validasi ISBN dan kode kategori unik.
- Proteksi CSRF pada form; Blade melakukan escaping output secara default.
- Eager loading relasi kategori pada daftar buku untuk mencegah N+1 query.
- Pagination, flash message, dan tampilan responsif Bootstrap.
- Seeder untuk 5 kategori dan 20 buku contoh.
- Audit query SQL ke `storage/logs/laravel.log`.

## Skema Database

### `kategoris`

| Kolom | Keterangan |
|---|---|
| `id` | Primary key |
| `kode_kategori` | String maksimal 10 karakter, unik |
| `nama_kategori` | String maksimal 100 karakter |
| `created_at`, `updated_at` | Timestamp |

### `bukus`

| Kolom | Keterangan |
|---|---|
| `id` | Primary key |
| `isbn` | String maksimal 20 karakter, unik |
| `judul` | String maksimal 255 karakter |
| `penulis` | String maksimal 150 karakter |
| `penerbit` | String maksimal 150 karakter |
| `tahun_terbit` | Tahun terbit empat digit |
| `kategori_id` | Foreign key ke `kategoris`; buku dihapus saat kategorinya dihapus |
| `stok` | Bilangan bulat minimal 0 |
| `sinopsis` | Teks opsional |
| `created_at`, `updated_at` | Timestamp |

## Persyaratan

- PHP 8.5 untuk menjalankan konfigurasi project ini (batas PHP pada `composer.json` adalah `^8.3`).
- Composer 2.6 atau lebih baru.
- Node.js 18 atau 20 dan npm.
- Ekstensi PDO SQLite aktif.

## Instalasi di Windows (PowerShell)

Dari folder tempat project akan disimpan, jalankan:

```powershell
git clone https://github.com/Dianitaginting/ppw-2026-week5-12S24044.git
Set-Location ppw-2026-week5-12S24044
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
npm run build
if (!(Test-Path database\database.sqlite)) {
    New-Item -ItemType File -Path database\database.sqlite
}
php artisan migrate:fresh --seed
php artisan serve
```

Perintah `migrate:fresh --seed` membuat ulang tabel dan memasukkan data contoh. Perintah ini menghapus tabel/data lama pada database yang dikonfigurasi; jangan jalankan pada database yang berisi data yang perlu dipertahankan.

Buka [http://127.0.0.1:8000](http://127.0.0.1:8000). Project menggunakan SQLite dan secara default menyimpan database di `database/database.sqlite`.

## Halaman Aplikasi

- `/buku` — daftar buku, detail, tambah, edit, dan hapus buku.
- `/kategori` — daftar kategori, detail, tambah, edit, dan hapus kategori.
- `/` — mengarahkan pengguna ke daftar buku.

Menghapus kategori juga menghapus buku terkait sesuai aturan foreign key `cascadeOnDelete`.

## Pengujian

Jalankan seluruh pengujian dengan:

```powershell
php artisan test
```

Tes fitur mencakup CRUD buku dan kategori, validasi input, penghapusan relasi, serta jumlah query daftar buku untuk memastikan eager loading mencegah N+1 query.

## Aset Frontend

Bootstrap dan aset CSS/JavaScript aplikasi dikelola melalui Vite. Untuk pengembangan frontend, jalankan `npm run dev` di PowerShell terpisah; untuk menghasilkan aset siap pakai, jalankan `npm run build`.

## Audit Query

Listener pada `AppServiceProvider` mencatat query SQL beserta waktu eksekusi ke:

```text
storage/logs/laravel.log
```

Gunakan log tersebut untuk memeriksa query yang dijalankan saat membuka halaman daftar buku.
