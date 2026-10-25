<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_layanan', function (Blueprint $table) {
            $table->text('uraian')->nullable()->after('sasaran');
        });

        DB::table('jurnal_layanan')
            ->select(['id', 'hasil', 'keterangan'])
            ->orderBy('id')
            ->chunkById(200, function ($jurnal) {
                foreach ($jurnal as $item) {
                    $bagian = array_filter([$item->hasil, $item->keterangan], fn ($nilai) => filled($nilai));
                    DB::table('jurnal_layanan')
                        ->where('id', $item->id)
                        ->update(['uraian' => implode(PHP_EOL, $bagian)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('jurnal_layanan', function (Blueprint $table) {
            $table->dropColumn('uraian');
        });
    }
};
