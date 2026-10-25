<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_accreditation_documents', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('kategori', 40);
            $table->string('periode', 30);
            $table->string('status', 20)->default('draft');
            $table->text('keterangan')->nullable();
            $table->string('nama_file', 255);
            $table->string('path_file', 500);
            $table->string('tipe_mime', 100);
            $table->unsignedBigInteger('ukuran_file');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['periode', 'kategori', 'status'], 'bk_evidence_period_category_status_idx');
        });

        Schema::create('bk_supervision_records', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 30);
            $table->string('periode', 30);
            $table->date('tanggal');
            $table->string('judul', 150);
            $table->text('temuan');
            $table->text('tindak_lanjut')->nullable();
            $table->date('batas_tindak_lanjut')->nullable();
            $table->string('status', 20)->default('planned');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['periode', 'jenis', 'status'], 'bk_supervision_period_type_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_supervision_records');
        Schema::dropIfExists('bk_accreditation_documents');
    }
};
