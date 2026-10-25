<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(file_get_contents(__DIR__.'/sql/tahap3_tables.sql'));
    }

    public function down(): void
    {
        DB::unprepared("
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE IF EXISTS surat, jenis_surat, asesmen_hasil, asesmen_jawaban_detail,
                asesmen_jawaban, asesmen_link, asesmen_butir, asesmen_instrumen, asesmen_jenis;
            SET FOREIGN_KEY_CHECKS=1;
        ");
    }
};
