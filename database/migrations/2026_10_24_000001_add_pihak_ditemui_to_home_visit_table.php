<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('home_visit', function (Blueprint $table) {
            $table->string('pihak_ditemui', 255)->nullable()->after('alamat_dikunjungi');
        });
    }

    public function down(): void
    {
        Schema::table('home_visit', function (Blueprint $table) {
            $table->dropColumn('pihak_ditemui');
        });
    }
};
