<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $types = [
        ['DCM', 'Daftar Cek Masalah (DCM)'],
        ['SOSIOMETRI', 'Sosiometri'],
        ['MOTIVASI_BELAJAR', 'Motivasi Belajar'],
        ['KEBIASAAN_BELAJAR', 'Kebiasaan Belajar'],
        ['PENYESUAIAN_DIRI', 'Penyesuaian Diri'],
        ['PERKEMBANGAN_SOSIAL', 'Perkembangan Sosial'],
        ['KARIER_SMP', 'Perencanaan Karier SMP'],
    ];

    public function up(): void
    {
        foreach ($this->types as [$code, $name]) {
            $existingType = DB::table('asesmen_jenis')->where('kode', $code)->first();
            if (!$existingType) {
                DB::table('asesmen_jenis')->insert([
                    'kode' => $code,
                    'nama' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($existingType->nama !== $name) {
                DB::table('asesmen_jenis')->where('id', $existingType->id)->update([
                    'nama' => $name,
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($this->instruments() as $instrument) {
            $typeId = DB::table('asesmen_jenis')->where('kode', $instrument['code'])->value('id');
            $existing = DB::table('asesmen_instrumen')
                ->where('asesmen_jenis_id', $typeId)
                ->where('nama_instrumen', $instrument['name'])
                ->first();

            if ($existing) {
                continue;
            }

            $instrumentId = DB::table('asesmen_instrumen')->insertGetId([
                'asesmen_jenis_id' => $typeId,
                'nama_instrumen' => $instrument['name'],
                'jenjang' => 'SMP',
                'keterangan' => 'Instrumen awal berdasarkan dokumen instrumen BK yang diberikan sekolah.',
                'konfigurasi_skor' => isset($instrument['configuration'])
                    ? json_encode($instrument['configuration'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
                    : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $items = [];
            foreach ($instrument['items'] as $index => [$category, $question]) {
                $items[] = [
                    'instrumen_id' => $instrumentId,
                    'nomor' => $index + 1,
                    'pertanyaan' => $question,
                    'kategori' => $category,
                    'bobot' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('asesmen_butir')->insert($items);
        }
    }

    public function down(): void
    {
        $names = array_column($this->instruments(), 'name');
        $unused = DB::table('asesmen_instrumen')
            ->whereIn('nama_instrumen', $names)
            ->whereNotIn('id', DB::table('asesmen_link')->select('instrumen_id'))
            ->whereNotIn('id', DB::table('asesmen_jawaban')->select('instrumen_id'))
            ->pluck('id');

        DB::table('asesmen_butir')->whereIn('instrumen_id', $unused)->delete();
        DB::table('asesmen_instrumen')->whereIn('id', $unused)->delete();

        $codes = array_column($this->types, 0);
        $unusedTypes = DB::table('asesmen_jenis')
            ->whereIn('kode', $codes)
            ->whereNotIn('id', DB::table('asesmen_instrumen')->select('asesmen_jenis_id'))
            ->pluck('id');
        DB::table('asesmen_jenis')->whereIn('id', $unusedTypes)->delete();
    }

    private function instruments(): array
    {
        $fourPoint = ['response_options' => ['1' => 'SS', '2' => 'S', '3' => 'TS', '4' => 'STS']];

        return [
            [
                'code' => 'DCM',
                'name' => 'Daftar Cek Masalah Murid (dokumen BK)',
                'items' => [
                    ['Pribadi', 'Saya sering merasa kurang percaya diri.'],
                    ['Pribadi', 'Saya sulit mengendalikan emosi ketika menghadapi masalah.'],
                    ['Pribadi', 'Saya sering merasa cemas atau khawatir.'],
                    ['Pribadi', 'Saya sulit mengambil keputusan sendiri.'],
                    ['Pribadi', 'Saya sering merasa bingung mengenai kelebihan dan kekurangan diri saya.'],
                    ['Pribadi', 'Saya mudah menyerah ketika menghadapi kesulitan.'],
                    ['Belajar', 'Saya sulit berkonsentrasi saat belajar.'],
                    ['Belajar', 'Saya sering menunda-nunda tugas sekolah.'],
                    ['Belajar', 'Saya kesulitan memahami pelajaran tertentu.'],
                    ['Belajar', 'Saya kurang memiliki motivasi untuk belajar.'],
                    ['Belajar', 'Saya sulit mengatur waktu antara belajar dan kegiatan lainnya.'],
                    ['Belajar', 'Nilai atau prestasi belajar saya menurun.'],
                    ['Belajar', 'Saya merasa takut menghadapi ujian atau tugas sekolah.'],
                    ['Sosial', 'Saya sulit bergaul atau berteman dengan murid lain.'],
                    ['Sosial', 'Saya sering merasa tidak diterima oleh teman-teman.'],
                    ['Sosial', 'Saya pernah mengalami konflik dengan teman.'],
                    ['Sosial', 'Saya merasa kesulitan menyampaikan pendapat kepada orang lain.'],
                    ['Sosial', 'Saya merasa tertekan karena pergaulan atau lingkungan teman.'],
                    ['Sosial', 'Saya sering merasa kesepian di sekolah.'],
                    ['Keluarga', 'Saya sering mengalami masalah atau konflik dengan orang tua.'],
                    ['Keluarga', 'Saya merasa kurang mendapatkan perhatian dari keluarga.'],
                    ['Keluarga', 'Kondisi keluarga membuat saya sulit berkonsentrasi belajar.'],
                    ['Keluarga', 'Saya merasa sulit menyampaikan masalah kepada keluarga.'],
                    ['Keluarga', 'Saya memiliki masalah dengan saudara di rumah.'],
                    ['Karier dan Masa Depan', 'Saya belum mengetahui minat dan bakat saya.'],
                    ['Karier dan Masa Depan', 'Saya bingung menentukan jurusan atau program pendidikan berikutnya.'],
                    ['Karier dan Masa Depan', 'Saya belum memiliki gambaran pekerjaan yang ingin saya pilih.'],
                    ['Karier dan Masa Depan', 'Saya membutuhkan informasi tentang sekolah, perguruan tinggi, atau dunia kerja.'],
                    ['Karier dan Masa Depan', 'Saya ragu terhadap pilihan pendidikan atau karier saya.'],
                    ['Kedisiplinan dan Perilaku', 'Saya sering terlambat datang ke sekolah.'],
                    ['Kedisiplinan dan Perilaku', 'Saya pernah tidak masuk sekolah tanpa alasan yang jelas.'],
                    ['Kedisiplinan dan Perilaku', 'Saya sulit mematuhi aturan sekolah.'],
                    ['Kedisiplinan dan Perilaku', 'Saya sering menggunakan HP/gawai secara berlebihan.'],
                    ['Kedisiplinan dan Perilaku', 'Saya kesulitan mengatur penggunaan media sosial.'],
                    ['Kedisiplinan dan Perilaku', 'Saya pernah melakukan tindakan yang saya sesali setelahnya.'],
                ],
                'configuration' => [
                    'response_options' => ['1' => 'Ya', '0' => 'Tidak'],
                    'open_responses' => [
                        'masalah_utama' => 'Masalah yang paling ingin saya bicarakan dengan Guru BK',
                        'harapan' => 'Hal yang saya harapkan dari layanan BK',
                    ],
                ],
            ],
            [
                'code' => 'SOSIOMETRI',
                'name' => 'Instrumen Sosiometri Murid (dokumen BK)',
                'items' => [
                    ['Pilihan Teman', 'Jika kamu mendapat tugas kelompok, kamu paling ingin satu kelompok dengan siapa? Pilih 3 teman.'],
                    ['Pilihan Teman', 'Jika kamu mengalami kesulitan dalam belajar, kamu paling ingin meminta bantuan kepada siapa? Pilih 3 teman.'],
                    ['Pilihan Teman', 'Jika ada kegiatan bersama di luar kelas, kamu paling ingin bersama dengan siapa? Pilih 3 teman.'],
                    ['Pilihan Teman', 'Jika kamu memiliki masalah pribadi dan membutuhkan teman untuk bercerita, kamu paling percaya kepada siapa? Pilih 3 teman.'],
                    ['Kerja Sama', 'Teman yang menurutmu paling bertanggung jawab dalam mengerjakan tugas adalah siapa? Pilih 3 teman.'],
                    ['Kerja Sama', 'Teman yang menurutmu paling mudah diajak bekerja sama adalah siapa? Pilih 3 teman.'],
                    ['Kerja Sama', 'Teman yang menurutmu paling mampu membantu kelompok ketika menghadapi kesulitan adalah siapa? Pilih 3 teman.'],
                    ['Hubungan Sosial', 'Teman yang paling sering kamu ajak berbicara atau bermain adalah siapa? Pilih 3 teman.'],
                    ['Hubungan Sosial', 'Teman yang membuatmu merasa nyaman ketika berada di kelas adalah siapa? Pilih 3 teman.'],
                    ['Hubungan Sosial', 'Teman yang menurutmu dapat menjadi ketua atau pemimpin kelompok adalah siapa? Pilih 3 teman.'],
                    ['Pertanyaan Terbuka', 'Dengan siapa kamu merasa paling nyaman berada dalam satu kelompok? Sebutkan nama dan alasannya.'],
                    ['Pertanyaan Terbuka', 'Apakah ada teman di kelas yang ingin lebih dekat atau lebih sering kamu ajak berteman? Sebutkan nama dan alasannya.'],
                    ['Pertanyaan Terbuka', 'Apakah kamu merasa kesulitan mendapatkan teman di kelas? Jelaskan jika ya.'],
                    ['Harapan', 'Apa yang kamu harapkan agar hubungan pertemanan di kelas menjadi lebih baik?'],
                ],
                'configuration' => [
                    'choice_items' => range(1, 10),
                    'open_items' => range(11, 14),
                    'choices_per_question' => 3,
                ],
            ],
            [
                'code' => 'MOTIVASI_BELAJAR',
                'name' => 'Instrumen Motivasi Belajar',
                'configuration' => array_merge($fourPoint, [
                    'reverse_items' => range(11, 20),
                    'ranges' => [
                        ['min' => 65, 'max' => 80, 'label' => 'Motivasi belajar sangat tinggi'],
                        ['min' => 50, 'max' => 64, 'label' => 'Motivasi belajar tinggi'],
                        ['min' => 35, 'max' => 49, 'label' => 'Motivasi belajar sedang'],
                        ['min' => 20, 'max' => 34, 'label' => 'Motivasi belajar rendah'],
                    ],
                    'aspects' => [
                        ['label' => 'Semangat dan ketekunan belajar', 'items' => [1, 2, 7, 11, 14, 16]],
                        ['label' => 'Orientasi terhadap prestasi', 'items' => [3, 4, 5, 6]],
                        ['label' => 'Kemandirian belajar', 'items' => [8, 9, 10, 15, 19]],
                        ['label' => 'Konsentrasi dan perhatian', 'items' => [17, 20]],
                        ['label' => 'Pengelolaan diri dalam belajar', 'items' => [12, 13, 18]],
                    ],
                    'follow_up' => [
                        'Semangat dan ketekunan belajar' => 'Berikan penguatan dan layanan untuk menjaga ketekunan belajar.',
                        'Orientasi terhadap prestasi' => 'Bantu murid menyusun target belajar yang realistis.',
                        'Kemandirian belajar' => 'Berikan layanan untuk meningkatkan konsistensi dan kemandirian belajar.',
                        'Konsentrasi dan perhatian' => 'Tinjau faktor yang mengganggu konsentrasi dan strategi pengelolaannya.',
                        'Pengelolaan diri dalam belajar' => 'Berikan layanan tentang pengelolaan waktu dan kebiasaan belajar.',
                    ],
                ]),
                'items' => [
                    ['Semangat dan ketekunan belajar', 'Saya bersemangat mengikuti kegiatan pembelajaran di sekolah.'],
                    ['Semangat dan ketekunan belajar', 'Saya berusaha memahami pelajaran meskipun materinya sulit.'],
                    ['Orientasi terhadap prestasi', 'Saya memiliki target yang ingin saya capai dalam belajar.'],
                    ['Orientasi terhadap prestasi', 'Saya merasa senang ketika berhasil memahami pelajaran.'],
                    ['Orientasi terhadap prestasi', 'Saya berusaha mendapatkan hasil belajar yang baik.'],
                    ['Orientasi terhadap prestasi', 'Saya belajar karena ingin mengembangkan kemampuan diri.'],
                    ['Semangat dan ketekunan belajar', 'Saya tetap belajar meskipun tidak ada ujian.'],
                    ['Kemandirian belajar', 'Saya berusaha menyelesaikan tugas tepat waktu.'],
                    ['Kemandirian belajar', 'Saya bertanya kepada guru ketika tidak memahami materi.'],
                    ['Kemandirian belajar', 'Saya mencari sumber belajar lain ketika mengalami kesulitan.'],
                    ['Semangat dan ketekunan belajar', 'Saya mudah kehilangan semangat ketika mendapat nilai rendah.'],
                    ['Pengelolaan diri dalam belajar', 'Saya sering menunda mengerjakan tugas sekolah.'],
                    ['Pengelolaan diri dalam belajar', 'Saya lebih suka bermain daripada belajar ketika memiliki waktu luang.'],
                    ['Semangat dan ketekunan belajar', 'Saya merasa malas belajar ketika pelajaran terasa sulit.'],
                    ['Kemandirian belajar', 'Saya belajar hanya jika diperintah oleh orang tua atau guru.'],
                    ['Semangat dan ketekunan belajar', 'Saya mudah menyerah ketika tidak langsung memahami pelajaran.'],
                    ['Konsentrasi dan perhatian', 'Saya sulit berkonsentrasi ketika belajar.'],
                    ['Pengelolaan diri dalam belajar', 'Saya sering tidak menyelesaikan tugas yang sudah diberikan.'],
                    ['Kemandirian belajar', 'Saya merasa tidak perlu belajar jika tidak ada ujian.'],
                    ['Konsentrasi dan perhatian', 'Saya sering menggunakan HP/gawai ketika seharusnya belajar.'],
                ],
            ],
            [
                'code' => 'KEBIASAAN_BELAJAR',
                'name' => 'Instrumen Kebiasaan Belajar',
                'configuration' => [
                    'response_options' => ['1' => 'SL', '2' => 'SR', '3' => 'KD', '4' => 'TP'],
                    'reverse_items' => [4, 5, 9, 12, 14, 15, 19, 24, 25, 28, 29],
                    'ranges' => [
                        ['min' => 98, 'max' => 120, 'label' => 'Kebiasaan belajar sangat baik'],
                        ['min' => 75, 'max' => 97, 'label' => 'Kebiasaan belajar baik'],
                        ['min' => 52, 'max' => 74, 'label' => 'Kebiasaan belajar cukup'],
                        ['min' => 30, 'max' => 51, 'label' => 'Kebiasaan belajar kurang'],
                    ],
                    'aspects' => [
                        ['label' => 'Pengaturan waktu belajar', 'start' => 1, 'end' => 5],
                        ['label' => 'Persiapan belajar', 'start' => 6, 'end' => 10],
                        ['label' => 'Konsentrasi belajar', 'start' => 11, 'end' => 15],
                        ['label' => 'Cara belajar', 'start' => 16, 'end' => 20],
                        ['label' => 'Mengerjakan tugas', 'start' => 21, 'end' => 25],
                        ['label' => 'Menghadapi ujian', 'start' => 26, 'end' => 30],
                    ],
                    'follow_up' => [
                        'Pengaturan waktu belajar' => 'Berikan layanan manajemen waktu dan penyusunan jadwal belajar.',
                        'Persiapan belajar' => 'Berikan layanan tentang lingkungan dan kesiapan belajar.',
                        'Konsentrasi belajar' => 'Tinjau latihan konsentrasi dan pengelolaan gangguan belajar.',
                        'Cara belajar' => 'Berikan layanan tentang strategi belajar efektif.',
                        'Mengerjakan tugas' => 'Berikan layanan tentang disiplin dan pengelolaan waktu.',
                        'Menghadapi ujian' => 'Berikan layanan tentang strategi menghadapi ujian.',
                    ],
                ],
                'items' => [
                    ['Pengaturan waktu belajar', 'Saya memiliki jadwal belajar di rumah.'],
                    ['Pengaturan waktu belajar', 'Saya belajar pada waktu yang hampir sama setiap hari.'],
                    ['Pengaturan waktu belajar', 'Saya menyempatkan waktu untuk belajar meskipun tidak ada ujian.'],
                    ['Pengaturan waktu belajar', 'Saya sering menunda waktu belajar.'],
                    ['Pengaturan waktu belajar', 'Saya belajar hanya ketika akan menghadapi ujian.'],
                    ['Persiapan belajar', 'Saya menyiapkan buku dan perlengkapan sebelum belajar.'],
                    ['Persiapan belajar', 'Saya mengetahui materi yang harus dipelajari.'],
                    ['Persiapan belajar', 'Saya memilih tempat yang nyaman untuk belajar.'],
                    ['Persiapan belajar', 'Saya belajar sambil menonton televisi atau bermain HP.'],
                    ['Persiapan belajar', 'Saya memastikan tempat belajar cukup tenang sebelum mulai belajar.'],
                    ['Konsentrasi belajar', 'Saya dapat berkonsentrasi ketika belajar.'],
                    ['Konsentrasi belajar', 'Saya mudah terganggu oleh suara atau aktivitas di sekitar.'],
                    ['Konsentrasi belajar', 'Saya tetap fokus sampai tugas belajar selesai.'],
                    ['Konsentrasi belajar', 'Saya sering memeriksa HP ketika sedang belajar.'],
                    ['Konsentrasi belajar', 'Saya berhenti belajar sebelum materi yang dipelajari selesai.'],
                    ['Cara belajar', 'Saya membuat catatan penting dari materi pelajaran.'],
                    ['Cara belajar', 'Saya membaca kembali materi yang sudah dipelajari.'],
                    ['Cara belajar', 'Saya mengulang pelajaran jika belum memahaminya.'],
                    ['Cara belajar', 'Saya menghafal materi tanpa berusaha memahaminya.'],
                    ['Cara belajar', 'Saya menggunakan berbagai sumber belajar untuk memahami materi.'],
                    ['Mengerjakan tugas', 'Saya mengerjakan tugas sesuai jadwal yang telah ditentukan.'],
                    ['Mengerjakan tugas', 'Saya memeriksa kembali tugas sebelum dikumpulkan.'],
                    ['Mengerjakan tugas', 'Saya mengerjakan tugas sendiri terlebih dahulu sebelum meminta bantuan.'],
                    ['Mengerjakan tugas', 'Saya sering mengerjakan tugas secara terburu-buru karena mendekati batas waktu.'],
                    ['Mengerjakan tugas', 'Saya sering lupa mengerjakan atau mengumpulkan tugas.'],
                    ['Menghadapi ujian', 'Saya mempersiapkan diri beberapa hari sebelum ujian.'],
                    ['Menghadapi ujian', 'Saya membuat jadwal belajar ketika akan menghadapi ujian.'],
                    ['Menghadapi ujian', 'Saya belajar dengan sistem kebut semalam sebelum ujian.'],
                    ['Menghadapi ujian', 'Saya merasa panik karena belum mempersiapkan ujian dengan baik.'],
                    ['Menghadapi ujian', 'Saya memanfaatkan hasil ujian untuk memperbaiki cara belajar saya.'],
                ],
            ],
            [
                'code' => 'PENYESUAIAN_DIRI',
                'name' => 'Instrumen Penyesuaian Diri',
                'configuration' => array_merge($fourPoint, [
                    'reverse_items' => [4, 6, 8, 12, 15, 20, 22, 24, 28, 30, 34, 36],
                    'ranges' => [
                        ['min' => 117, 'max' => 144, 'label' => 'Penyesuaian diri sangat baik'],
                        ['min' => 90, 'max' => 116, 'label' => 'Penyesuaian diri baik'],
                        ['min' => 63, 'max' => 89, 'label' => 'Penyesuaian diri cukup'],
                        ['min' => 36, 'max' => 62, 'label' => 'Penyesuaian diri kurang'],
                    ],
                    'aspects' => [
                        ['label' => 'Penyesuaian terhadap diri sendiri', 'start' => 1, 'end' => 8],
                        ['label' => 'Penyesuaian di sekolah', 'start' => 9, 'end' => 16],
                        ['label' => 'Penyesuaian dalam pertemanan', 'start' => 17, 'end' => 24],
                        ['label' => 'Penyesuaian dalam keluarga', 'start' => 25, 'end' => 30],
                        ['label' => 'Penyesuaian terhadap lingkungan dan perubahan', 'start' => 31, 'end' => 36],
                    ],
                    'follow_up' => [
                        'Penyesuaian terhadap diri sendiri' => 'Pertimbangkan konseling individual dan penguatan konsep diri.',
                        'Penyesuaian di sekolah' => 'Pertimbangkan layanan klasikal atau konseling terkait adaptasi sekolah.',
                        'Penyesuaian dalam pertemanan' => 'Pertimbangkan bimbingan sosial dan latihan keterampilan komunikasi.',
                        'Penyesuaian dalam keluarga' => 'Pertimbangkan konseling dan kolaborasi dengan orang tua/wali bila diperlukan.',
                        'Penyesuaian terhadap lingkungan dan perubahan' => 'Pertimbangkan layanan pengelolaan emosi, pemecahan masalah, dan adaptasi.',
                    ],
                ]),
                'items' => [
                    ['Penyesuaian terhadap diri sendiri', 'Saya dapat menerima kelebihan dan kekurangan diri saya.'],
                    ['Penyesuaian terhadap diri sendiri', 'Saya merasa nyaman menjadi diri sendiri.'],
                    ['Penyesuaian terhadap diri sendiri', 'Saya percaya bahwa saya memiliki kemampuan yang dapat dikembangkan.'],
                    ['Penyesuaian terhadap diri sendiri', 'Saya mudah merasa rendah diri ketika melihat kelebihan orang lain.'],
                    ['Penyesuaian terhadap diri sendiri', 'Saya dapat mengendalikan emosi ketika menghadapi masalah.'],
                    ['Penyesuaian terhadap diri sendiri', 'Saya mudah menyerah ketika menghadapi kegagalan.'],
                    ['Penyesuaian terhadap diri sendiri', 'Saya mampu mengambil keputusan setelah mempertimbangkan akibatnya.'],
                    ['Penyesuaian terhadap diri sendiri', 'Saya sering menyalahkan diri sendiri ketika melakukan kesalahan.'],
                    ['Penyesuaian di sekolah', 'Saya merasa nyaman berada di lingkungan sekolah.'],
                    ['Penyesuaian di sekolah', 'Saya dapat mengikuti aturan dan tata tertib sekolah.'],
                    ['Penyesuaian di sekolah', 'Saya dapat menyesuaikan diri dengan guru dan tenaga kependidikan.'],
                    ['Penyesuaian di sekolah', 'Saya merasa sulit mengikuti kebiasaan atau aturan baru di sekolah.'],
                    ['Penyesuaian di sekolah', 'Saya berani bertanya kepada guru jika mengalami kesulitan.'],
                    ['Penyesuaian di sekolah', 'Saya dapat mengikuti kegiatan sekolah dengan baik.'],
                    ['Penyesuaian di sekolah', 'Saya merasa tidak nyaman ketika berada di kelas.'],
                    ['Penyesuaian di sekolah', 'Saya dapat menerima kritik atau saran dari guru.'],
                    ['Penyesuaian dalam pertemanan', 'Saya mudah berkenalan dengan teman baru.'],
                    ['Penyesuaian dalam pertemanan', 'Saya dapat bekerja sama dengan teman yang berbeda karakter.'],
                    ['Penyesuaian dalam pertemanan', 'Saya dapat menghargai pendapat teman meskipun berbeda dengan pendapat saya.'],
                    ['Penyesuaian dalam pertemanan', 'Saya sulit memulai percakapan dengan teman baru.'],
                    ['Penyesuaian dalam pertemanan', 'Saya dapat menyelesaikan perselisihan dengan teman secara baik.'],
                    ['Penyesuaian dalam pertemanan', 'Saya sering merasa tidak diterima oleh teman-teman.'],
                    ['Penyesuaian dalam pertemanan', 'Saya bersedia membantu teman yang mengalami kesulitan.'],
                    ['Penyesuaian dalam pertemanan', 'Saya mudah tersinggung ketika mendapat kritik dari teman.'],
                    ['Penyesuaian dalam keluarga', 'Saya merasa nyaman berkomunikasi dengan orang tua atau wali.'],
                    ['Penyesuaian dalam keluarga', 'Saya dapat menyampaikan pendapat kepada keluarga dengan baik.'],
                    ['Penyesuaian dalam keluarga', 'Saya berusaha memahami dan menghargai aturan di rumah.'],
                    ['Penyesuaian dalam keluarga', 'Saya sering mengalami konflik dengan anggota keluarga.'],
                    ['Penyesuaian dalam keluarga', 'Saya dapat menceritakan masalah yang saya hadapi kepada keluarga.'],
                    ['Penyesuaian dalam keluarga', 'Saya merasa tidak nyaman ketika berada di rumah.'],
                    ['Penyesuaian terhadap lingkungan dan perubahan', 'Saya dapat menyesuaikan diri ketika berada di lingkungan yang baru.'],
                    ['Penyesuaian terhadap lingkungan dan perubahan', 'Saya mampu menghadapi perubahan tanpa terlalu merasa khawatir.'],
                    ['Penyesuaian terhadap lingkungan dan perubahan', 'Saya mau mencoba kegiatan atau pengalaman baru.'],
                    ['Penyesuaian terhadap lingkungan dan perubahan', 'Saya membutuhkan waktu yang sangat lama untuk menyesuaikan diri dengan lingkungan baru.'],
                    ['Penyesuaian terhadap lingkungan dan perubahan', 'Saya dapat mencari solusi ketika menghadapi situasi yang tidak sesuai dengan harapan.'],
                    ['Penyesuaian terhadap lingkungan dan perubahan', 'Saya cenderung menghindari situasi yang membuat saya tidak nyaman.'],
                ],
            ],
            [
                'code' => 'PERKEMBANGAN_SOSIAL',
                'name' => 'Instrumen Perkembangan Sosial',
                'configuration' => array_merge($fourPoint, [
                    'reverse_items' => [4, 6, 10, 12, 16, 18, 22, 24, 28, 30, 34],
                    'ranges' => [
                        ['min' => 117, 'max' => 144, 'label' => 'Perkembangan sosial sangat baik'],
                        ['min' => 90, 'max' => 116, 'label' => 'Perkembangan sosial baik'],
                        ['min' => 63, 'max' => 89, 'label' => 'Perkembangan sosial cukup'],
                        ['min' => 36, 'max' => 62, 'label' => 'Perkembangan sosial perlu pengembangan'],
                    ],
                    'aspects' => [
                        ['label' => 'Interaksi sosial', 'start' => 1, 'end' => 6],
                        ['label' => 'Komunikasi sosial', 'start' => 7, 'end' => 12],
                        ['label' => 'Kerja sama', 'start' => 13, 'end' => 18],
                        ['label' => 'Empati dan kepedulian', 'start' => 19, 'end' => 24],
                        ['label' => 'Pengelolaan konflik', 'start' => 25, 'end' => 30],
                        ['label' => 'Tanggung jawab dan norma sosial', 'start' => 31, 'end' => 36],
                    ],
                    'follow_up' => [
                        'Interaksi sosial' => 'Pertimbangkan bimbingan kelompok tentang keterampilan pergaulan.',
                        'Komunikasi sosial' => 'Pertimbangkan latihan komunikasi asertif dan mendengarkan aktif.',
                        'Kerja sama' => 'Pertimbangkan kegiatan kelompok dan latihan kerja sama.',
                        'Empati dan kepedulian' => 'Pertimbangkan kegiatan pengembangan empati dan kepedulian sosial.',
                        'Pengelolaan konflik' => 'Pertimbangkan latihan penyelesaian konflik secara konstruktif.',
                        'Tanggung jawab dan norma sosial' => 'Pertimbangkan layanan tentang norma, tanggung jawab, dan perilaku prososial.',
                    ],
                ]),
                'items' => [
                    ['Interaksi sosial', 'Saya mudah berkenalan dengan teman baru.'],
                    ['Interaksi sosial', 'Saya dapat memulai percakapan dengan teman.'],
                    ['Interaksi sosial', 'Saya merasa nyaman berada di lingkungan teman sebaya.'],
                    ['Interaksi sosial', 'Saya sering menghindari kegiatan bersama teman.'],
                    ['Interaksi sosial', 'Saya dapat berteman dengan murid yang memiliki karakter berbeda.'],
                    ['Interaksi sosial', 'Saya merasa sulit mendapatkan teman.'],
                    ['Komunikasi sosial', 'Saya dapat menyampaikan pendapat dengan sopan.'],
                    ['Komunikasi sosial', 'Saya mau mendengarkan ketika teman sedang berbicara.'],
                    ['Komunikasi sosial', 'Saya dapat menyampaikan ketidaksetujuan tanpa menyinggung perasaan orang lain.'],
                    ['Komunikasi sosial', 'Saya sering memotong pembicaraan orang lain.'],
                    ['Komunikasi sosial', 'Saya dapat meminta bantuan kepada orang lain ketika membutuhkannya.'],
                    ['Komunikasi sosial', 'Saya merasa takut menyampaikan pendapat di depan teman-teman.'],
                    ['Kerja sama', 'Saya senang bekerja sama dalam kelompok.'],
                    ['Kerja sama', 'Saya mau berbagi tugas dengan anggota kelompok.'],
                    ['Kerja sama', 'Saya menghargai kontribusi setiap anggota kelompok.'],
                    ['Kerja sama', 'Saya sulit menerima pendapat anggota kelompok yang berbeda dengan saya.'],
                    ['Kerja sama', 'Saya bersedia membantu teman yang mengalami kesulitan.'],
                    ['Kerja sama', 'Saya lebih suka mengerjakan tugas sendiri daripada bekerja sama.'],
                    ['Empati dan kepedulian', 'Saya berusaha memahami perasaan teman.'],
                    ['Empati dan kepedulian', 'Saya peduli ketika melihat teman mengalami kesulitan.'],
                    ['Empati dan kepedulian', 'Saya dapat menghargai perbedaan pendapat, kebiasaan, dan latar belakang teman.'],
                    ['Empati dan kepedulian', 'Saya sering mengabaikan teman yang sedang membutuhkan bantuan.'],
                    ['Empati dan kepedulian', 'Saya dapat menjaga perasaan teman ketika berbicara.'],
                    ['Empati dan kepedulian', 'Saya senang mengejek teman yang melakukan kesalahan.'],
                    ['Pengelolaan konflik', 'Saya dapat menyelesaikan masalah dengan teman melalui pembicaraan.'],
                    ['Pengelolaan konflik', 'Saya bersedia meminta maaf ketika melakukan kesalahan.'],
                    ['Pengelolaan konflik', 'Saya dapat memaafkan teman yang mengakui kesalahannya.'],
                    ['Pengelolaan konflik', 'Saya mudah marah ketika pendapat saya tidak diterima.'],
                    ['Pengelolaan konflik', 'Saya berusaha mencari jalan keluar yang adil ketika terjadi perselisihan.'],
                    ['Pengelolaan konflik', 'Saya cenderung membalas ketika ada teman yang menyakiti saya.'],
                    ['Tanggung jawab dan norma sosial', 'Saya berusaha menaati aturan yang berlaku di sekolah.'],
                    ['Tanggung jawab dan norma sosial', 'Saya menghormati guru, teman, dan warga sekolah lainnya.'],
                    ['Tanggung jawab dan norma sosial', 'Saya menjaga ucapan ketika berbicara dengan orang lain.'],
                    ['Tanggung jawab dan norma sosial', 'Saya sering melakukan sesuatu tanpa memikirkan dampaknya terhadap orang lain.'],
                    ['Tanggung jawab dan norma sosial', 'Saya bertanggung jawab terhadap tugas yang diberikan kepada saya.'],
                    ['Tanggung jawab dan norma sosial', 'Saya tetap menghargai orang lain meskipun berbeda pendapat dengan saya.'],
                ],
            ],
            [
                'code' => 'KARIER_SMP',
                'name' => 'Instrumen Perencanaan Karier SMP',
                'configuration' => array_merge($fourPoint, [
                    'reverse_items' => [6, 12, 18, 23, 24, 30, 36],
                    'ranges' => [
                        ['min' => 117, 'max' => 144, 'label' => 'Perencanaan karier sangat siap'],
                        ['min' => 90, 'max' => 116, 'label' => 'Perencanaan karier siap'],
                        ['min' => 63, 'max' => 89, 'label' => 'Perencanaan karier cukup siap'],
                        ['min' => 36, 'max' => 62, 'label' => 'Perencanaan karier belum siap'],
                    ],
                    'aspects' => [
                        ['label' => 'Pemahaman diri', 'start' => 1, 'end' => 6],
                        ['label' => 'Eksplorasi pendidikan lanjutan', 'start' => 7, 'end' => 12],
                        ['label' => 'Pengenalan dunia kerja', 'start' => 13, 'end' => 18],
                        ['label' => 'Pengambilan keputusan karier', 'start' => 19, 'end' => 24],
                        ['label' => 'Perencanaan dan persiapan', 'start' => 25, 'end' => 30],
                        ['label' => 'Dukungan dan kesiapan', 'start' => 31, 'end' => 36],
                    ],
                    'follow_up' => [
                        'Pemahaman diri' => 'Berikan layanan pengenalan minat, bakat, potensi, dan nilai diri.',
                        'Eksplorasi pendidikan lanjutan' => 'Berikan informasi pilihan sekolah lanjutan dan jurusan.',
                        'Pengenalan dunia kerja' => 'Berikan layanan informasi dan eksplorasi bidang pekerjaan.',
                        'Pengambilan keputusan karier' => 'Berikan bimbingan karier dan latihan pengambilan keputusan.',
                        'Perencanaan dan persiapan' => 'Bantu murid menyusun tujuan dan langkah konkret setelah lulus SMP.',
                        'Dukungan dan kesiapan' => 'Pertimbangkan konseling individual atau bimbingan kelompok sesuai kebutuhan.',
                    ],
                ]),
                'items' => [
                    ['Pemahaman diri', 'Saya mengetahui kelebihan yang saya miliki.'],
                    ['Pemahaman diri', 'Saya mengetahui kekurangan yang perlu saya perbaiki.'],
                    ['Pemahaman diri', 'Saya mengetahui mata pelajaran yang paling saya sukai.'],
                    ['Pemahaman diri', 'Saya mengetahui minat atau kegiatan yang paling saya sukai.'],
                    ['Pemahaman diri', 'Saya mengetahui kemampuan yang dapat menjadi modal untuk masa depan.'],
                    ['Pemahaman diri', 'Saya masih bingung mengenai minat dan kemampuan diri saya.'],
                    ['Eksplorasi pendidikan lanjutan', 'Saya mengetahui pilihan sekolah atau pendidikan setelah lulus SMP.'],
                    ['Eksplorasi pendidikan lanjutan', 'Saya mengetahui perbedaan SMA, SMK, dan pilihan pendidikan lainnya yang relevan.'],
                    ['Eksplorasi pendidikan lanjutan', 'Saya mencari informasi tentang jurusan atau program yang sesuai dengan minat saya.'],
                    ['Eksplorasi pendidikan lanjutan', 'Saya mengetahui persyaratan untuk masuk ke sekolah yang saya inginkan.'],
                    ['Eksplorasi pendidikan lanjutan', 'Saya berdiskusi dengan orang tua atau guru mengenai pilihan pendidikan saya.'],
                    ['Eksplorasi pendidikan lanjutan', 'Saya belum mengetahui harus memilih sekolah lanjutan yang mana.'],
                    ['Pengenalan dunia kerja', 'Saya mengetahui beberapa jenis pekerjaan yang sesuai dengan minat saya.'],
                    ['Pengenalan dunia kerja', 'Saya mengetahui keterampilan yang dibutuhkan dalam pekerjaan yang saya minati.'],
                    ['Pengenalan dunia kerja', 'Saya mencari informasi mengenai berbagai jenis pekerjaan.'],
                    ['Pengenalan dunia kerja', 'Saya memahami bahwa pilihan pendidikan dapat berkaitan dengan pilihan karier.'],
                    ['Pengenalan dunia kerja', 'Saya mengetahui pekerjaan yang ingin saya lakukan di masa depan.'],
                    ['Pengenalan dunia kerja', 'Saya belum memiliki gambaran mengenai pekerjaan yang ingin saya pilih.'],
                    ['Pengambilan keputusan karier', 'Saya mampu mempertimbangkan kelebihan dan kekurangan dari pilihan pendidikan.'],
                    ['Pengambilan keputusan karier', 'Saya mempertimbangkan minat dan kemampuan ketika menentukan pilihan.'],
                    ['Pengambilan keputusan karier', 'Saya mempertimbangkan informasi yang saya peroleh sebelum mengambil keputusan.'],
                    ['Pengambilan keputusan karier', 'Saya berani menentukan pilihan setelah mempertimbangkannya dengan baik.'],
                    ['Pengambilan keputusan karier', 'Saya mudah mengikuti pilihan teman meskipun belum tentu sesuai dengan diri saya.'],
                    ['Pengambilan keputusan karier', 'Saya merasa takut salah ketika harus menentukan pilihan sekolah lanjutan.'],
                    ['Perencanaan dan persiapan', 'Saya sudah memiliki gambaran mengenai sekolah lanjutan yang ingin saya pilih.'],
                    ['Perencanaan dan persiapan', 'Saya memiliki tujuan yang ingin dicapai setelah lulus SMP.'],
                    ['Perencanaan dan persiapan', 'Saya berusaha meningkatkan nilai atau kemampuan yang mendukung tujuan saya.'],
                    ['Perencanaan dan persiapan', 'Saya memiliki rencana untuk mengembangkan minat dan bakat saya.'],
                    ['Perencanaan dan persiapan', 'Saya mencari informasi dan mempersiapkan diri untuk mencapai tujuan saya.'],
                    ['Perencanaan dan persiapan', 'Saya belum pernah membuat rencana mengenai pendidikan dan karier saya.'],
                    ['Dukungan dan kesiapan', 'Saya bersedia berdiskusi dengan guru BK mengenai rencana masa depan saya.'],
                    ['Dukungan dan kesiapan', 'Saya mau menerima saran dari orang tua dalam merencanakan masa depan.'],
                    ['Dukungan dan kesiapan', 'Saya terbuka untuk mencari informasi dari berbagai sumber.'],
                    ['Dukungan dan kesiapan', 'Saya bersedia mengubah rencana jika mendapatkan informasi yang lebih sesuai.'],
                    ['Dukungan dan kesiapan', 'Saya merasa yakin bahwa saya dapat mempersiapkan masa depan saya secara bertahap.'],
                    ['Dukungan dan kesiapan', 'Saya merasa tidak perlu memikirkan rencana karier karena masih terlalu dini.'],
                ],
            ],
        ];
    }
};
