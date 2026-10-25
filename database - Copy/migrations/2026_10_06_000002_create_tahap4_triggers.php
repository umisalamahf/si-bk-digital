<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Trigger ini menegaskan aturan "Isi RPL per Jenis" (lihat ERD bagian 3) di level database:
// - RPL konseling_individual/konseling_kelompok WAJIB isi kolom masalah
// - RPL klasikal WAJIB pilih kelas
// Dicek juga di Laravel (FormRequest) untuk pesan error yang lebih ramah,
// tapi trigger ini jaring pengaman terakhir kalau ada jalur input lain.
return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(file_get_contents(__DIR__.'/sql/tahap4_logic.sql'));
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_before_update_rpl;
            DROP TRIGGER IF EXISTS trg_before_insert_rpl;
        ");
    }
};
