
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menambahkan batasan nilai status peminjaman.
     */
    public function up(): void
    {
        DB::statement("
            CREATE TRIGGER peminjaman_status_insert_check
            BEFORE INSERT ON peminjamen
            FOR EACH ROW
            WHEN NEW.status NOT IN ('dipinjam', 'dikembalikan')
            BEGIN
                SELECT RAISE(ABORT, 'Status peminjaman tidak valid.');
            END;
        ");

        DB::statement("
            CREATE TRIGGER peminjaman_status_update_check
            BEFORE UPDATE OF status ON peminjamen
            FOR EACH ROW
            WHEN NEW.status NOT IN ('dipinjam', 'dikembalikan')
            BEGIN
                SELECT RAISE(ABORT, 'Status peminjaman tidak valid.');
            END;
        ");
    }

    /**
     * Menghapus batasan status ketika migrasi dibatalkan.
     */
    public function down(): void
    {
        DB::statement(
            'DROP TRIGGER IF EXISTS peminjaman_status_insert_check'
        );

        DB::statement(
            'DROP TRIGGER IF EXISTS peminjaman_status_update_check'
        );
    }
};
