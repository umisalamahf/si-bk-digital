<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Migration ini pakai DB::unprepared() berisi SQL mentah (bukan Schema::create),
// supaya PERSIS sama dengan SQL yang sudah DICOBA LANGSUNG di MariaDB dan TERBUKTI jalan.
// Kenapa begini: lebih aman daripada nulis ulang pakai Schema::create() dengan tangan,
// karena ada trigger & kolom yang detailnya gampang salah ketik kalau ditulis ulang.
return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(file_get_contents(__DIR__.'/sql/tahap1_tables.sql'));
    }

    public function down(): void
    {
        DB::unprepared("
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE IF EXISTS audit_log, poin_siswa_rekap, poin_siswa, jenis_reward,
                jenis_pelanggaran, catatan_anekdot, absensi, siswa, kelas, guru_wali,
                guru_bk, sekolah, users;
            SET FOREIGN_KEY_CHECKS=1;
        ");
    }
};
