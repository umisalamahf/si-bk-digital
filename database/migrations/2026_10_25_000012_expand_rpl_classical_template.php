<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->string('kepala_sekolah_nip', 30)->nullable()->after('kepala_sekolah');
        });

        Schema::table('rpl', function (Blueprint $table) {
            $table->string('satuan_pendidikan', 150)->nullable();
            $table->string('kelas_fase', 100)->nullable();
            $table->string('tahun_ajaran', 12)->nullable();
            $table->string('jumlah_jam_pertemuan', 100)->nullable();
            $table->text('kesiapan_peserta_didik')->nullable();
            $table->text('karakteristik_materi')->nullable();
            $table->text('dimensi_profil_lulusan')->nullable();
            $table->text('capaian_layanan')->nullable();
            $table->text('lintas_disiplin_ilmu')->nullable();
            $table->text('tujuan_layanan_bk')->nullable();
            $table->text('topik_layanan')->nullable();
            $table->text('praktik_pedagogik')->nullable();
            $table->text('kemitraan_lingkungan_pembelajaran')->nullable();
            $table->text('pemanfaatan_digital')->nullable();
            $table->text('kegiatan_awal')->nullable();
            $table->text('kegiatan_inti')->nullable();
            $table->text('kegiatan_penutup')->nullable();
            $table->text('asesmen_awal')->nullable();
            $table->text('asesmen_proses')->nullable();
            $table->text('asesmen_akhir')->nullable();
            $table->string('nama_kepala_sekolah', 150)->nullable();
            $table->string('nip_kepala_sekolah', 30)->nullable();
            $table->string('nama_guru_bk', 150)->nullable();
            $table->string('nip_guru_bk', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rpl', function (Blueprint $table) {
            $table->dropColumn([
                'satuan_pendidikan',
                'kelas_fase',
                'tahun_ajaran',
                'jumlah_jam_pertemuan',
                'kesiapan_peserta_didik',
                'karakteristik_materi',
                'dimensi_profil_lulusan',
                'capaian_layanan',
                'lintas_disiplin_ilmu',
                'tujuan_layanan_bk',
                'topik_layanan',
                'praktik_pedagogik',
                'kemitraan_lingkungan_pembelajaran',
                'pemanfaatan_digital',
                'kegiatan_awal',
                'kegiatan_inti',
                'kegiatan_penutup',
                'asesmen_awal',
                'asesmen_proses',
                'asesmen_akhir',
                'nama_kepala_sekolah',
                'nip_kepala_sekolah',
                'nama_guru_bk',
                'nip_guru_bk',
            ]);
        });

        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn('kepala_sekolah_nip');
        });
    }
};
