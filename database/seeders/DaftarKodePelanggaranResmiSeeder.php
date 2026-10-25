<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DaftarKodePelanggaranResmiSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->violations() as [$code, $group, $name, $points, $handling]) {
                $existing = DB::table('jenis_pelanggaran')->where('kode', $code)->first();
                $severity = $this->severityFor($points);

                if ($existing && (
                    $existing->nama_pelanggaran !== $name ||
                    (int) $existing->poin !== $points ||
                    $existing->kategori !== $severity
                )) {
                    throw new RuntimeException(
                        "Kode pelanggaran {$code} sudah ada dengan data berbeda. Seeder dihentikan untuk mencegah perubahan catatan poin yang sudah tersimpan."
                    );
                }

                $values = [
                    'nama_pelanggaran' => $name,
                    'kategori' => $severity,
                    'kategori_kelompok' => $group,
                    'penanganan' => $handling,
                    'poin' => $points,
                    'updated_at' => now(),
                ];

                if ($existing) {
                    DB::table('jenis_pelanggaran')->where('id', $existing->id)->update($values);
                } else {
                    DB::table('jenis_pelanggaran')->insert($values + [
                        'kode' => $code,
                        'created_at' => now(),
                    ]);
                }
            }

            foreach ($this->pointRules() as [$min, $max, $followUp, $parties]) {
                $existingRule = DB::table('aturan_akumulasi_poin')
                    ->where('poin_min', $min)
                    ->where('poin_max', $max)
                    ->first();
                $ruleValues = [
                        'tindak_lanjut' => $followUp,
                        'pihak_terlibat' => $parties,
                        'updated_at' => now(),
                    ];

                if ($existingRule) {
                    DB::table('aturan_akumulasi_poin')->where('id', $existingRule->id)->update($ruleValues);
                } else {
                    DB::table('aturan_akumulasi_poin')->insert($ruleValues + [
                        'poin_min' => $min,
                        'poin_max' => $max,
                        'created_at' => now(),
                    ]);
                }
            }
        });
    }

    private function severityFor(int $points): string
    {
        return match (true) {
            $points <= 5 => 'ringan',
            $points <= 15 => 'sedang',
            default => 'berat',
        };
    }

    private function violations(): array
    {
        return [
            ['1.1', 'Kedisiplinan & Kehadiran', 'Siswa datang lebih dari pukul 06.35', 2, 'Peneguran'],
            ['1.2', 'Kedisiplinan & Kehadiran', 'Tidak mengikuti upacara bendera', 5, 'Pembinaan'],
            ['1.3', 'Kedisiplinan & Kehadiran', 'Tidak mengikuti pembiasaan rutin', 5, 'Pembinaan'],
            ['1.4', 'Kedisiplinan & Kehadiran', 'Membolos/kabur pada saat pembelajaran', 5, 'Pemanggilan ortu dan pembinaan'],
            ['1.5', 'Kedisiplinan & Kehadiran', 'Siswa terlambat maks 3 kali dalam seminggu', 5, 'Mengikuti pembinaan bersama walas dan guru BK'],
            ['1.6', 'Kedisiplinan & Kehadiran', 'Pergi ke luar gerbang tanpa membawa surat izin dari guru piket', 5, 'Teguran'],
            ['1.7', 'Kedisiplinan & Kehadiran', 'Alpa lebih dari maks 3 x dalam sebulan', 10, 'Pemanggilan ortu dan pembinaan'],

            ['2.1', 'Kerapian, Seragam, & Kebersihan', 'Tidak menggunakan atribut lengkap (topi, sepatu, bet, sabuk, kaos kaki, dan dasi)', 2, 'Ditegur'],
            ['2.2', 'Kerapian, Seragam, & Kebersihan', 'Tidak memasukan baju kedalam rok/celana (tidak rapi)', 2, 'Ditegur'],
            ['2.3', 'Kerapian, Seragam, & Kebersihan', 'Menggunakan make up ke sekolah', 2, 'Ditegur'],
            ['2.4', 'Kerapian, Seragam, & Kebersihan', 'Buang sampah sembarangan', 2, 'Ditegur'],
            ['2.5', 'Kerapian, Seragam, & Kebersihan', 'Membawa make up ke sekolah', 2, 'Ditegur'],
            ['2.6', 'Kerapian, Seragam, & Kebersihan', 'Menggunakan sepatu tidak sesuai dengan ketentuan sekolah', 5, 'Ditegur dan disimpan sepatunya di Ruang BK'],
            ['2.7', 'Kerapian, Seragam, & Kebersihan', 'Mencoret coret baju seragam dan topi sekolah', 5, 'Ditegur'],
            ['2.8', 'Kerapian, Seragam, & Kebersihan', 'Rambut tidak rapi/gondrong (siswa laki-laki) atau mewarnai rambut', 5, 'Dicatat dan teguran'],

            ['3.1', 'Etika, Perilaku, & Pornografi', 'Mengucapkan kata-kata kasar / tidak sopan / kotor sesama siswa/guru/warga sekolah lainnya', 2, 'Diingatkan / Ditegur'],
            ['3.2', 'Etika, Perilaku, & Pornografi', 'Melakukan kegiatan Pornografi secara offline/online', 15, 'Pemanggilan ortu dan Pembinaan'],
            ['3.3', 'Etika, Perilaku, & Pornografi', 'Menyimpan video tidak senonoh (Pornografi) dan menyebarluaskannya di dalam HP', 15, 'Pemanggilan ortu dan Pembinaan'],
            ['3.4', 'Etika, Perilaku, & Pornografi', 'Berpacaran di lingkungan sekolah', 15, 'Pemanggilan ortu dan pembinaan'],
            ['4.1', 'Fasilitas & Berkendara', 'Membawa motor ke sekolah', 10, 'Pemanggilan orang tua'],
            ['4.2', 'Fasilitas & Berkendara', 'Menggunakan knalpot racing/brong', 10, 'Pemanggilan orang tua'],
            ['4.3', 'Fasilitas & Berkendara', 'Merusak fasilitas sekolah', 15, 'Pemanggilan ortu dan pembinaan'],

            ['5.1', 'Pelanggaran Hak Milik & Kriminalitas', 'Mengambil barang milik orang lain (alat tulis)', 5, 'Dicatat dan teguran'],
            ['5.2', 'Pelanggaran Hak Milik & Kriminalitas', 'Melakukan pemalakan terhadap orang lain', 10, 'Pemanggilan ortu dan pembinaan'],
            ['5.3', 'Pelanggaran Hak Milik & Kriminalitas', 'Melakukan kegiatan yang berkaitan dengan Judi On Line (Judi Online) dalam bentuk apapun', 15, 'Pemanggilan ortu dan pembinaan'],
            ['5.4', 'Pelanggaran Hak Milik & Kriminalitas', 'Mengambil HP atau uang milik orang lain', 25, 'Pemanggilan ortu dan pembinaan'],
            ['5.5', 'Pelanggaran Hak Milik & Kriminalitas', 'Mencuri', 40, 'Pemanggilan ortu dan tindakan khusus'],

            ['6.1', 'Kekerasan, Perundungan, & Zat Terlarang', 'Membawa senjata tajam ke sekolah', 10, 'Pemanggilan orang tua'],
            ['6.2', 'Kekerasan, Perundungan, & Zat Terlarang', 'Melakukan tindakan bullying baik secara verbal dan non verbal terhadap sesama siswa/siswi', 15, 'Pemanggilan ortu dan pembinaan'],
            ['6.3', 'Kekerasan, Perundungan, & Zat Terlarang', 'Melakukan pelecehan di lingkungan sekolah', 15, 'Pemanggilan ortu dan pembinaan'],
            ['6.4', 'Kekerasan, Perundungan, & Zat Terlarang', 'Terlibat perkelahian sesama siswa/siswi dan menyebabkan siswa lain terluka', 25, 'Pemanggilan ortu dan pembinaan'],
            ['6.5', 'Kekerasan, Perundungan, & Zat Terlarang', 'Terlibat tawuran di dalam/luar lingkungan sekolah', 25, 'Pemanggilan ortu dan pembinaan'],
            ['6.6', 'Kekerasan, Perundungan, & Zat Terlarang', 'Membawa/menjual/merokok baik di sekolah maupun di luar sekolah', 25, 'Pemanggilan ortu dan pembinaan'],
            ['6.7', 'Kekerasan, Perundungan, & Zat Terlarang', 'Mengkonsumsi/membawa NAPZA atau minuman keras ke sekolah', 25, 'Pemanggilan ortu dan pembinaan'],
            ['6.8', 'Kekerasan, Perundungan, & Zat Terlarang', 'Membuat/mengoleksi/menyebarkan konten pornografi', 50, 'Pemanggilan ortu dan tindakan khusus'],
        ];
    }

    private function pointRules(): array
    {
        return [
            [1, 25, 'Perjanjian tertulis + catatan wali kelas', 'BK, Wali Kelas'],
            [26, 50, 'Peringatan tertulis + membuat surat SP bersama orang tua', 'BK, Wali Kelas'],
            [51, 75, 'Peringatan tertulis + dan membuat surat SP bersama orang tua', 'BK, Wali Kelas'],
            [76, 99, 'Rencana Pendampingan khusus', 'BK, Wali Kelas, Wakasek Kesiswaan'],
            [100, 100, 'Bimbingan Lanjutan / Rekomendasi Pindah sekolah / Alih tangan kasus', 'Wakasek Kesiswaan'],
        ];
    }
}
