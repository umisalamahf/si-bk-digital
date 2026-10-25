<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jenis_pelanggaran', function (Blueprint $table) {
            $table->string('kode_pelanggaran', 20)->nullable()->storedAs('kode')->after('kode');
            $table->integer('bobot_point')->storedAs('poin')->after('poin');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_pelanggaran', function (Blueprint $table) {
            $table->dropColumn(['kode_pelanggaran', 'bobot_point']);
        });
    }
};
