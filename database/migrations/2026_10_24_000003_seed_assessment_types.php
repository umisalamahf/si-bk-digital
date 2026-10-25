<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $types = [
        ['kode' => 'AKPD', 'nama' => 'AKPD'],
        ['kode' => 'GAYA_BELAJAR', 'nama' => 'Gaya Belajar'],
        ['kode' => 'BAKAT_MINAT', 'nama' => 'Tes Bakat Minat'],
        ['kode' => 'SOSIOMETRI', 'nama' => 'Sosiometri'],
        ['kode' => 'DCM', 'nama' => 'Daftar Cek Masalah (DCM)'],
    ];

    public function up(): void
    {
        foreach ($this->types as $type) {
            DB::table('asesmen_jenis')->updateOrInsert(
                ['kode' => $type['kode']],
                ['nama' => $type['nama'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $unusedIds = DB::table('asesmen_jenis')
            ->whereIn('kode', array_column($this->types, 'kode'))
            ->whereNotIn('id', DB::table('asesmen_instrumen')->select('asesmen_jenis_id'))
            ->pluck('id');

        DB::table('asesmen_jenis')->whereIn('id', $unusedIds)->delete();
    }
};
