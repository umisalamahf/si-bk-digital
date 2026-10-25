<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!DB::table('jenis_surat')->where('kode', 'SPO')->exists()) {
            DB::table('jenis_surat')->insert([
                'kode' => 'SPO',
                'nama_surat' => 'Surat Pemanggilan Orang Tua',
                'urutan_terakhir' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $jenisSuratId = DB::table('jenis_surat')->where('kode', 'SPO')->value('id');
        if ($jenisSuratId && !DB::table('surat')->where('jenis_surat_id', $jenisSuratId)->exists()) {
            DB::table('jenis_surat')->where('id', $jenisSuratId)->delete();
        }
    }
};
