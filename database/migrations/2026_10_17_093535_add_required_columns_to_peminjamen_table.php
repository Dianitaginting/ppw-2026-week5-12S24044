<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjamen', function (Blueprint $table) {
            $table->string('id_kode_pinjam')->nullable()->unique();
            $table->string('nim_peminjam')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();
        });

        // Lengkapi data transaksi lama agar tidak kehilangan riwayat.
        $peminjamans = DB::table('peminjamen')->get();

        foreach ($peminjamans as $peminjaman) {
            $tanggalPinjam = Carbon::parse($peminjaman->tanggal_pinjam);

            DB::table('peminjamen')
                ->where('id', $peminjaman->id)
                ->update([
                    'id_kode_pinjam' => 'PJM-' . Str::upper(Str::random(10)),
                    'nim_peminjam' => 'LEGACY-' . $peminjaman->id,
                    'tanggal_jatuh_tempo' => $tanggalPinjam
                        ->copy()
                        ->addDays(7)
                        ->toDateString(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('peminjamen', function (Blueprint $table) {
            $table->dropUnique(['id_kode_pinjam']);

            $table->dropColumn([
                'id_kode_pinjam',
                'nim_peminjam',
                'tanggal_jatuh_tempo',
            ]);
        });
    }
};
