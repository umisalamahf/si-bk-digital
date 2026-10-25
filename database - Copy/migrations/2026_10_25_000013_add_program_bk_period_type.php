<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('program_bk', function (Blueprint $table) {
            $table->string('jenis_program', 20)->default('tahunan')->after('tahun_ajaran');
        });
    }

    public function down(): void
    {
        Schema::table('program_bk', function (Blueprint $table) {
            $table->dropColumn('jenis_program');
        });
    }
};
