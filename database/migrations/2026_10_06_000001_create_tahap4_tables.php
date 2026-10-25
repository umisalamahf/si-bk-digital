<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(file_get_contents(__DIR__.'/sql/tahap4_tables.sql'));
    }

    public function down(): void
    {
        DB::unprepared("
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE IF EXISTS jurnal_layanan, evaluasi_layanan, rpl_siswa, rpl, program_bk;
            SET FOREIGN_KEY_CHECKS=1;
        ");
    }
};
