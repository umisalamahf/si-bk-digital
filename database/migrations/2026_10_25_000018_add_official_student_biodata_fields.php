<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->string('nama_panggilan', 100)->nullable()->after('nama');
            $table->unsignedSmallInteger('no_absen')->nullable()->after('kelas_id');
            $table->string('agama', 50)->nullable()->after('jenis_kelamin');
            $table->string('no_hp_ayah', 20)->nullable()->after('pekerjaan_ayah');
            $table->string('no_hp_ibu', 20)->nullable()->after('pekerjaan_ibu');
            $table->string('no_hp_wali', 20)->nullable()->after('hubungan_wali');
            $table->string('hobi_minat', 1000)->nullable()->after('penghasilan_orang_tua');
            $table->string('bakat_potensi', 1000)->nullable()->after('hobi_minat');
            $table->string('cita_cita', 500)->nullable()->after('bakat_potensi');
            $table->text('kondisi_khusus')->nullable()->after('cita_cita');
            $table->text('catatan_guru_bk')->nullable()->after('kondisi_khusus');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn([
                'nama_panggilan',
                'no_absen',
                'agama',
                'no_hp_ayah',
                'no_hp_ibu',
                'no_hp_wali',
                'hobi_minat',
                'bakat_potensi',
                'cita_cita',
                'kondisi_khusus',
                'catatan_guru_bk',
            ]);
        });
    }
};
