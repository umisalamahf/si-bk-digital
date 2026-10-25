<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angket_kepuasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpl_id')->constrained('rpl')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('respon_kepuasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('angket_kepuasan_id')->constrained('angket_kepuasan')->cascadeOnDelete();
            $table->string('kategori_responden', 30);
            $table->json('jawaban');
            $table->timestamps();
        });

        Schema::create('rpl_pelaksanaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpl_id')->unique()->constrained('rpl')->cascadeOnDelete();
            $table->unsignedSmallInteger('jumlah_hadir');
            $table->text('hasil_pelaksanaan');
            $table->text('hasil_layanan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rpl_pelaksanaan');
        Schema::dropIfExists('respon_kepuasan');
        Schema::dropIfExists('angket_kepuasan');
    }
};
