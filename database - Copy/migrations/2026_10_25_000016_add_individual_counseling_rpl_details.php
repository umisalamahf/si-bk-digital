<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rpl', function (Blueprint $table) {
            $table->string('kelas_semester', 100)->nullable()->after('tanggal');
            $table->unsignedSmallInteger('pertemuan_ke')->nullable()->after('kelas_semester');
            $table->string('waktu', 50)->nullable()->after('pertemuan_ke');
            $table->string('tempat', 200)->nullable()->after('waktu');
        });
    }

    public function down(): void
    {
        Schema::table('rpl', function (Blueprint $table) {
            $table->dropColumn(['kelas_semester', 'pertemuan_ke', 'waktu', 'tempat']);
        });
    }
};
