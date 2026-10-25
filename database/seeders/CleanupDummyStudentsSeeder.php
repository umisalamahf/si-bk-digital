<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CleanupDummyStudentsSeeder extends Seeder
{
    private const DUMMY_NIS = [
        '2026001',
        '2026002',
        '2026003',
        '2026004',
        '2026005',
        '12234567',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $students = DB::table('siswa')
                ->whereIn('nis', self::DUMMY_NIS)
                ->lockForUpdate()
                ->get(['id', 'nis', 'nama', 'user_id']);

            $allowed = [
                '2026001' => 'Andi Saputra',
                '2026002' => 'Bunga Lestari',
                '2026003' => 'Citra Dewi',
                '2026004' => 'Dedi Kurniawan',
                '2026005' => 'Eka Putri',
                '12234567' => 'Siti Aisyah Rahmawati',
            ];

            foreach ($students as $student) {
                if (($allowed[$student->nis] ?? null) !== $student->nama) {
                    throw new RuntimeException(
                        "NIS {$student->nis} tidak cocok dengan identitas dummy yang diharapkan; tidak ada data dihapus."
                    );
                }
            }

            if ($students->isEmpty()) {
                $this->command?->info('Tidak ada data siswa dummy yang perlu dibersihkan.');
                return;
            }

            $studentIds = $students->pluck('id')->all();
            $this->deleteWhereIn('asesmen_jawaban_pilihan', 'siswa_dipilih_id', $studentIds);
            $this->deleteWhereIn('absensi', 'siswa_id', $studentIds);
            $this->deleteWhereIn('ajuan_konseling', 'siswa_id', $studentIds);
            $this->deleteWhereIn('alumni', 'siswa_id', $studentIds);
            $this->deleteWhereIn('asesmen_jawaban', 'siswa_id', $studentIds);
            $this->deleteWhereIn('bk_formulir', 'siswa_id', $studentIds);
            $this->deleteWhereIn('bk_formulir', 'siswa_pihak_2_id', $studentIds);
            $this->deleteWhereIn('catatan_anekdot', 'siswa_id', $studentIds);
            $this->deleteWhereIn('home_visit', 'siswa_id', $studentIds);
            $this->deleteWhereIn('jadwal_layanan', 'siswa_id', $studentIds);
            $this->deleteWhereIn('nilai_siswa', 'siswa_id', $studentIds);
            $this->deleteWhereIn('poin_siswa', 'siswa_id', $studentIds);
            $this->deleteWhereIn('poin_siswa_rekap', 'siswa_id', $studentIds);
            $this->deleteWhereIn('rpl_siswa', 'siswa_id', $studentIds);
            $this->deleteWhereIn('surat', 'siswa_id', $studentIds);

            $userIds = $students->pluck('user_id')->filter()->unique()->all();
            DB::table('siswa')->whereIn('id', $studentIds)->delete();
            if ($userIds !== []) {
                DB::table('users')->whereIn('id', $userIds)->where('role', 'siswa')->delete();
            }

            $this->command?->info(
                "Data dummy dibersihkan: {$students->count()} siswa. Roster Excel dan struktur kelas tetap dipertahankan."
            );
        });
    }

    private function deleteWhereIn(string $table, string $column, array $studentIds): void
    {
        DB::table($table)->whereIn($column, $studentIds)->delete();
    }
}
