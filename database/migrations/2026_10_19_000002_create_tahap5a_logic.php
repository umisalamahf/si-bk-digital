<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Trigger alumni + procedure kelulusan & kenaikan kelas.
// Tanpa DELIMITER dan diawali DROP IF EXISTS (pelajaran dari migrate:fresh sebelumnya).
return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(file_get_contents(__DIR__.'/sql/tahap5a_logic.sql'));
    }

    public function down(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_kenaikan_kelas;
            DROP PROCEDURE IF EXISTS sp_kelulusan_kelas;
            DROP TRIGGER IF EXISTS trg_after_update_siswa_status;
        ");
    }
};
