<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!DB::table('jenis_surat')->where('kode', 'SKB')->exists()) {
            DB::table('jenis_surat')->insert([
                'kode' => 'SKB',
                'nama_surat' => 'Surat Keterangan Kelakuan Baik',
                'urutan_terakhir' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $jenisSuratId = DB::table('jenis_surat')->where('kode', 'SKB')->value('id');
        if ($jenisSuratId && !DB::table('surat')->where('jenis_surat_id', $jenisSuratId)->exists()) {
            DB::table('jenis_surat')->where('id', $jenisSuratId)->delete();
        }
    }
};
