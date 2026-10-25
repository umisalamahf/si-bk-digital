<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asesmen_instrumen', function (Blueprint $table) {
            $table->json('konfigurasi_skor')->nullable()->after('keterangan');
        });

        Schema::table('asesmen_hasil', function (Blueprint $table) {
            $table->json('detail_skor')->nullable()->after('rekomendasi');
        });

        Schema::table('asesmen_jawaban', function (Blueprint $table) {
            $table->json('respons_teks')->nullable()->after('asesmen_link_id');
        });

        Schema::table('asesmen_jawaban_pilihan', function (Blueprint $table) {
            $table->unsignedTinyInteger('urutan_pilihan')->nullable()->after('siswa_dipilih_id');
        });

        Schema::create('bk_formulir', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 30);
            $table->unsignedBigInteger('siswa_id')->nullable();
            $table->unsignedBigInteger('siswa_pihak_2_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->date('tanggal');
            $table->json('data');
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->restrictOnDelete();
            $table->foreign('siswa_pihak_2_id')->references('id')->on('siswa')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['jenis', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_formulir');

        Schema::table('asesmen_jawaban', function (Blueprint $table) {
            $table->dropColumn('respons_teks');
        });

        Schema::table('asesmen_jawaban_pilihan', function (Blueprint $table) {
            $table->dropColumn('urutan_pilihan');
        });

        Schema::table('asesmen_hasil', function (Blueprint $table) {
            $table->dropColumn('detail_skor');
        });

        Schema::table('asesmen_instrumen', function (Blueprint $table) {
            $table->dropColumn('konfigurasi_skor');
        });
    }
};
