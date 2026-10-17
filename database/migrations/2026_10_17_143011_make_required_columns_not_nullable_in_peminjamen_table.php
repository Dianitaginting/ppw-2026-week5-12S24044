
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjamen', function (Blueprint $table) {
            $table->string('id_kode_pinjam')
                ->nullable(false)
                ->change();

            $table->string('nim_peminjam')
                ->nullable(false)
                ->change();

            $table->date('tanggal_jatuh_tempo')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('peminjamen', function (Blueprint $table) {
            $table->string('id_kode_pinjam')
                ->nullable()
                ->change();

            $table->string('nim_peminjam')
                ->nullable()
                ->change();

            $table->date('tanggal_jatuh_tempo')
                ->nullable()
                ->change();
        });
    }
};
