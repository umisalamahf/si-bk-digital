<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','guru_bk','wali_kelas','kepala_sekolah','siswa') NOT NULL");

        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained('users')->restrictOnDelete();
        });

        Schema::create('slot_konseling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_bk_user_id')->constrained('users')->restrictOnDelete();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('tempat', 150)->nullable();
            $table->enum('status', ['tersedia', 'dipesan', 'ditutup'])->default('tersedia');
            $table->timestamps();
            $table->unique(['guru_bk_user_id', 'tanggal', 'jam_mulai'], 'slot_konseling_guru_tanggal_mulai_unique');
            $table->index(['tanggal', 'status']);
        });

        Schema::create('ajuan_konseling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_konseling_id')->constrained('slot_konseling')->restrictOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->restrictOnDelete();
            $table->text('keperluan')->nullable();
            $table->enum('status', ['dipesan', 'selesai', 'dibatalkan'])->default('dipesan');
            $table->timestamps();
            $table->index(['siswa_id', 'status']);
            $table->index(['slot_konseling_id', 'status']);
        });
    }

    public function down(): void
    {
        if (DB::table('users')->where('role', 'siswa')->exists()) {
            throw new RuntimeException('Hapus atau ubah akun siswa sebelum rollback migration ini.');
        }

        Schema::dropIfExists('ajuan_konseling');
        Schema::dropIfExists('slot_konseling');

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','guru_bk','wali_kelas','kepala_sekolah') NOT NULL");
    }
};
