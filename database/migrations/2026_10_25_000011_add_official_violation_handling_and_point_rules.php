<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jenis_pelanggaran', function (Blueprint $table) {
            $table->string('kategori_kelompok', 100)->nullable()->after('kategori');
            $table->text('penanganan')->nullable()->after('bobot_point');
        });

        Schema::create('aturan_akumulasi_poin', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('poin_min');
            $table->unsignedInteger('poin_max');
            $table->text('tindak_lanjut');
            $table->string('pihak_terlibat', 150);
            $table->timestamps();

            $table->unique(['poin_min', 'poin_max'], 'aturan_akumulasi_rentang_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aturan_akumulasi_poin');

        Schema::table('jenis_pelanggaran', function (Blueprint $table) {
            $table->dropColumn(['kategori_kelompok', 'penanganan']);
        });
    }
};
