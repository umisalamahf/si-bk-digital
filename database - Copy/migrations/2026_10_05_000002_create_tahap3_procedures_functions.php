<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Sama seperti Fase 1: TIDAK pakai DELIMITER (itu cuma trik client mysql CLI,
// PDO/Laravel tidak butuh itu). Semua di sini juga TANPA JSON_TABLE,
// jadi aman di MariaDB 10.4 (XAMPP) maupun hosting yang lebih baru nanti.
return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(file_get_contents(__DIR__.'/sql/tahap3_logic.sql'));
    }

    public function down(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_generate_nomor_surat;
            DROP PROCEDURE IF EXISTS sp_hitung_hasil_asesmen;
            DROP PROCEDURE IF EXISTS sp_generate_link_asesmen;
            DROP FUNCTION IF EXISTS fn_format_nomor_surat;
            DROP FUNCTION IF EXISTS fn_cek_sudah_isi_asesmen;
        ");
    }
};
