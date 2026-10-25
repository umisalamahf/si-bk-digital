<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// PENTING buat dijelaskan ke guru:
// Procedure/Trigger/Function di sini ditulis TANPA JSON_TABLE, supaya jalan di MariaDB 10.4
// (versi yang dipakai XAMPP di laptop). JSON_TABLE baru ada mulai MariaDB 10.6.
// Kalau nanti hosting online sudah pakai MySQL 8 / MariaDB 10.6+, kode ini TETAP jalan normal,
// jadi aman dipakai di laptop maupun hosting.
return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(file_get_contents(__DIR__.'/sql/tahap1_logic.sql'));
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_after_insert_catatan_anekdot;
            DROP TRIGGER IF EXISTS trg_audit_delete_siswa;
            DROP TRIGGER IF EXISTS trg_audit_update_siswa;
            DROP TRIGGER IF EXISTS trg_before_insert_siswa;
            DROP TRIGGER IF EXISTS trg_after_delete_poin_siswa;
            DROP TRIGGER IF EXISTS trg_after_update_poin_siswa;
            DROP TRIGGER IF EXISTS trg_after_insert_poin_siswa;
            DROP TRIGGER IF EXISTS trg_before_insert_poin_siswa;
            DROP PROCEDURE IF EXISTS sp_rekap_absensi;
            DROP PROCEDURE IF EXISTS sp_rekap_poin_siswa;
            DROP FUNCTION IF EXISTS fn_hitung_usia;
            DROP FUNCTION IF EXISTS fn_status_tindak_lanjut;
        ");
    }
};
