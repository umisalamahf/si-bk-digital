<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asesmen_jawaban', function (Blueprint $table) {
            $table->unsignedBigInteger('asesmen_link_id')->nullable()->after('instrumen_id');
            $table->foreign('asesmen_link_id')->references('id')->on('asesmen_link')->nullOnDelete();
        });

        Schema::create('asesmen_jawaban_pilihan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jawaban_id');
            $table->unsignedBigInteger('butir_id');
            $table->unsignedBigInteger('siswa_dipilih_id');
            $table->timestamps();
            $table->unique(['jawaban_id', 'butir_id', 'siswa_dipilih_id'], 'asesmen_pilihan_unik');
            $table->foreign('jawaban_id')->references('id')->on('asesmen_jawaban')->cascadeOnDelete();
            $table->foreign('butir_id')->references('id')->on('asesmen_butir')->restrictOnDelete();
            $table->foreign('siswa_dipilih_id')->references('id')->on('siswa')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesmen_jawaban_pilihan');

        Schema::table('asesmen_jawaban', function (Blueprint $table) {
            $table->dropForeign(['asesmen_link_id']);
            $table->dropColumn('asesmen_link_id');
        });
    }
};
