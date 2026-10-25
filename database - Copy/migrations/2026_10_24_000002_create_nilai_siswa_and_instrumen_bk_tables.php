<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nilai_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->string('mata_pelajaran', 100);
            $table->enum('semester', ['ganjil', 'genap']);
            $table->decimal('nilai', 5, 2);
            $table->string('tahun_ajaran', 12);
            $table->timestamps();
            $table->unique(['siswa_id', 'mata_pelajaran', 'semester', 'tahun_ajaran'], 'nilai_siswa_periode_unique');
            $table->foreign('siswa_id')->references('id')->on('siswa')->restrictOnDelete();
        });

        Schema::create('instrumen_bk', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('kategori', 100);
            $table->text('keterangan')->nullable();
            $table->string('nama_file', 255);
            $table->string('path_file', 500);
            $table->string('tipe_mime', 100);
            $table->unsignedBigInteger('ukuran_file');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrumen_bk');
        Schema::dropIfExists('nilai_siswa');
    }
};
