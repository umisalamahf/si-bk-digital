<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private const SEED_MARKER = 'Seed refleksi diri BK (2026_10_25_000021).';

    public function up(): void
    {
        foreach ($this->instruments() as $instrument) {
            $type = DB::table('asesmen_jenis')->where('kode', $instrument['code'])->first();
            if (!$type) {
                $typeId = DB::table('asesmen_jenis')->insertGetId([
                    'kode' => $instrument['code'],
                    'nama' => $instrument['type_name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $typeId = $type->id;
            }

            $exists = DB::table('asesmen_instrumen')
                ->where('asesmen_jenis_id', $typeId)
                ->where('nama_instrumen', $instrument['name'])
                ->exists();
            if ($exists) {
                continue;
            }

            $items = [];
            $aspects = [];
            $number = 1;
            foreach ($instrument['aspects'] as $aspect) {
                $numbers = [];
                foreach ($aspect['questions'] as $question) {
                    $items[] = [
                        'number' => $number,
                        'category' => $aspect['label'],
                        'question' => $question,
                    ];
                    $numbers[] = $number++;
                }
                $aspects[] = ['label' => $aspect['label'], 'items' => $numbers];
            }

            $configuration = [
                'scoring_mode' => 'profile',
                'result_label' => $instrument['result_label'],
                'response_options' => ['1' => 'SS', '2' => 'S', '3' => 'TS', '4' => 'STS'],
                'aspects' => $aspects,
                'aspect_levels' => [
                    ['min' => 4, 'max' => 8, 'label' => 'Belum banyak terasa saat ini'],
                    ['min' => 9, 'max' => 12, 'label' => 'Mulai terasa dan dapat dieksplorasi'],
                    ['min' => 13, 'max' => 16, 'label' => 'Sering terasa sesuai saat ini'],
                ],
                'disclaimer' => $instrument['disclaimer'],
                'reflection_prompt' => $instrument['reflection_prompt'],
            ];

            $instrumentId = DB::table('asesmen_instrumen')->insertGetId([
                'asesmen_jenis_id' => $typeId,
                'nama_instrumen' => $instrument['name'],
                'jenjang' => 'SMP',
                'keterangan' => self::SEED_MARKER . ' ' . $instrument['description'],
                'konfigurasi_skor' => json_encode(
                    $configuration,
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                ),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('asesmen_butir')->insert(array_map(
                fn (array $item) => [
                    'instrumen_id' => $instrumentId,
                    'nomor' => $item['number'],
                    'pertanyaan' => $item['question'],
                    'kategori' => $item['category'],
                    'bobot' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                $items
            ));
        }
    }

    public function down(): void
    {
        $definitions = $this->instruments();
        foreach ($definitions as $instrument) {
            $typeId = DB::table('asesmen_jenis')->where('kode', $instrument['code'])->value('id');
            if (!$typeId) {
                continue;
            }

            $seeded = DB::table('asesmen_instrumen')
                ->where('asesmen_jenis_id', $typeId)
                ->where('nama_instrumen', $instrument['name'])
                ->where('keterangan', self::SEED_MARKER . ' ' . $instrument['description'])
                ->get(['id']);

            foreach ($seeded as $row) {
                $inUse = DB::table('asesmen_link')->where('instrumen_id', $row->id)->exists()
                    || DB::table('asesmen_jawaban')->where('instrumen_id', $row->id)->exists();
                if ($inUse) {
                    continue;
                }

                DB::table('asesmen_butir')->where('instrumen_id', $row->id)->delete();
                DB::table('asesmen_instrumen')->where('id', $row->id)->delete();
            }
        }
    }

    private function instruments(): array
    {
        $disclaimer = 'Ini adalah bahan eksplorasi dan refleksi diri, bukan tes bakat/kemampuan yang terstandar, diagnosis, atau penetapan label tetap. Jawaban berdasarkan persepsi saat ini; hasil tidak mengukur kemampuan objektif dan sebaiknya dibahas bersama Guru BK serta dibandingkan dengan pengalaman nyata.';

        return [
            [
                'code' => 'MINAT_BAKAT',
                'type_name' => 'Eksplorasi Minat dan Kekuatan',
                'name' => 'Eksplorasi Minat dan Kekuatan yang Dirasakan',
                'result_label' => 'Profil eksplorasi minat',
                'description' => 'Pernyataan reflektif untuk membantu murid mengeksplorasi minat dan kekuatan yang dirasakan pada enam area RIASEC; bukan tes bakat terstandar.',
                'disclaimer' => $disclaimer,
                'reflection_prompt' => 'Pilih satu atau dua area yang terasa menarik untuk dicoba lebih lanjut. Diskusikan kegiatan, pengalaman, dan pilihan belajar bersama Guru BK; skor tidak membuktikan bakat atau menentukan karier.',
                'aspects' => [
                    ['label' => 'Praktis dan teknis', 'questions' => [
                        'Saya tertarik mencoba kegiatan yang melibatkan alat, bahan, atau membuat sesuatu.',
                        'Saya menikmati tugas praktik yang hasilnya dapat dilihat atau digunakan.',
                        'Saya ingin belajar keterampilan teknis melalui latihan langsung.',
                        'Saya merasa tertarik pada kegiatan luar ruang atau pengamatan lingkungan.',
                    ]],
                    ['label' => 'Penyelidikan dan analisis', 'questions' => [
                        'Saya senang mencari tahu alasan suatu peristiwa dapat terjadi.',
                        'Saya menikmati mengamati, membandingkan, atau mengelompokkan informasi.',
                        'Saya tertarik memecahkan teka-teki atau masalah dengan beberapa langkah.',
                        'Saya ingin mencoba percobaan sederhana untuk menjawab rasa ingin tahu.',
                    ]],
                    ['label' => 'Kreatif dan ekspresif', 'questions' => [
                        'Saya senang membuat karya dengan gambar, tulisan, musik, gerak, atau media lain.',
                        'Saya tertarik menemukan cara yang berbeda untuk menyampaikan sebuah gagasan.',
                        'Saya menikmati kegiatan yang memberi ruang untuk berimajinasi.',
                        'Saya ingin mencoba kegiatan seni atau desain yang belum pernah saya lakukan.',
                    ]],
                    ['label' => 'Membantu dan bekerja sama', 'questions' => [
                        'Saya senang membantu teman memahami sesuatu yang sedang dipelajari.',
                        'Saya tertarik mendengarkan dan memahami sudut pandang orang lain.',
                        'Saya menikmati kegiatan yang dikerjakan bersama untuk mencapai tujuan.',
                        'Saya ingin terlibat dalam kegiatan yang memberi manfaat bagi orang lain.',
                    ]],
                    ['label' => 'Memimpin dan menginisiasi', 'questions' => [
                        'Saya tertarik mengusulkan ide ketika kelompok mencari jalan keluar.',
                        'Saya menikmati mengajak teman bekerja menuju tujuan bersama.',
                        'Saya ingin belajar menyampaikan pendapat atau gagasan di depan kelompok.',
                        'Saya tertarik merencanakan kegiatan sederhana bersama teman.',
                    ]],
                    ['label' => 'Teratur dan mengelola informasi', 'questions' => [
                        'Saya senang menata catatan, bahan, atau perlengkapan agar mudah ditemukan.',
                        'Saya menikmati mengikuti langkah kerja yang jelas dan runtut.',
                        'Saya tertarik mencatat data atau informasi dengan rapi.',
                        'Saya merasa terbantu dengan jadwal dan daftar tugas yang teratur.',
                    ]],
                ],
            ],
            [
                'code' => 'KECERDASAN_MAJEMUK',
                'type_name' => 'Refleksi Preferensi Aktivitas Belajar',
                'name' => 'Refleksi Kecerdasan Majemuk: Preferensi Aktivitas Belajar',
                'result_label' => 'Profil preferensi aktivitas belajar',
                'description' => 'Pernyataan reflektif terinspirasi delapan area kecerdasan majemuk untuk membahas preferensi aktivitas; bukan pengukuran IQ atau kecerdasan terstandar.',
                'disclaimer' => 'Kerangka delapan area ini dipakai sebagai pemantik refleksi, bukan ukuran IQ, diagnosis, peringkat kecerdasan, atau bukti bakat. Setiap orang dapat belajar melalui banyak cara; jawaban menggambarkan preferensi yang dirasakan saat ini dan bukan label tetap.',
                'reflection_prompt' => 'Gunakan area yang terasa sesuai untuk mencoba beragam strategi belajar. Tidak ada area yang lebih baik dari yang lain; bicarakan pengalaman dan strategi yang membantu bersama Guru BK atau guru mata pelajaran.',
                'aspects' => [
                    ['label' => 'Bahasa dan cerita', 'questions' => [
                        'Saya menikmati membaca cerita, artikel, atau informasi tertulis.',
                        'Saya senang menjelaskan gagasan dengan berbicara atau menulis.',
                        'Saya tertarik bermain dengan kata, membuat cerita, atau merangkum bacaan.',
                        'Saya lebih mudah memahami materi ketika dapat membaca atau mendiskusikannya.',
                    ]],
                    ['label' => 'Logika dan pola', 'questions' => [
                        'Saya menikmati mencari pola, hubungan, atau urutan dalam suatu informasi.',
                        'Saya tertarik memecahkan soal atau teka-teki langkah demi langkah.',
                        'Saya suka membandingkan bukti sebelum menarik kesimpulan.',
                        'Saya terbantu dengan tabel, angka, atau pengelompokan untuk memahami materi.',
                    ]],
                    ['label' => 'Visual dan ruang', 'questions' => [
                        'Saya menikmati belajar menggunakan gambar, peta, diagram, atau video.',
                        'Saya senang membuat sketsa atau tata letak untuk menjelaskan ide.',
                        'Saya tertarik memperhatikan bentuk, warna, dan posisi suatu objek.',
                        'Saya lebih mudah mengingat informasi yang disajikan secara visual.',
                    ]],
                    ['label' => 'Gerak dan praktik', 'questions' => [
                        'Saya menikmati belajar dengan mencoba atau mempraktikkan secara langsung.',
                        'Saya tertarik menggunakan gerakan atau peragaan untuk menjelaskan sesuatu.',
                        'Saya lebih mudah memahami langkah kerja setelah mencobanya sendiri.',
                        'Saya senang membuat atau merakit sesuatu sebagai bagian dari belajar.',
                    ]],
                    ['label' => 'Musik dan bunyi', 'questions' => [
                        'Saya menikmati memperhatikan irama, nada, atau pola bunyi.',
                        'Saya senang belajar melalui lagu, ritme, atau pengulangan bunyi.',
                        'Saya tertarik mencoba alat musik atau membuat bunyi sederhana.',
                        'Saya mudah mengingat sebagian informasi ketika disampaikan dengan irama.',
                    ]],
                    ['label' => 'Memahami dan bekerja dengan orang lain', 'questions' => [
                        'Saya menikmati bertukar ide dan belajar bersama teman.',
                        'Saya tertarik memahami perasaan atau sudut pandang orang lain.',
                        'Saya senang membantu kelompok membagi tugas dan bekerja sama.',
                        'Saya dapat belajar dari diskusi atau kegiatan kelompok.',
                    ]],
                    ['label' => 'Mengenali diri dan belajar mandiri', 'questions' => [
                        'Saya senang memikirkan hal yang sudah saya pahami dan yang masih ingin dipelajari.',
                        'Saya terbantu dengan tujuan belajar yang saya tentukan sendiri.',
                        'Saya menikmati waktu tenang untuk membaca atau berlatih secara mandiri.',
                        'Saya dapat menyebutkan cara belajar yang terasa cocok bagi saya.',
                    ]],
                    ['label' => 'Alam dan lingkungan', 'questions' => [
                        'Saya tertarik mengamati tumbuhan, hewan, cuaca, atau lingkungan sekitar.',
                        'Saya menikmati mengelompokkan atau membandingkan benda-benda alam.',
                        'Saya senang belajar melalui pengamatan langsung di lingkungan.',
                        'Saya tertarik memahami cara menjaga alam dan lingkungan.',
                    ]],
                ],
            ],
            [
                'code' => 'REFLEKSI_DIRI',
                'type_name' => 'Refleksi Diri',
                'name' => 'Kuesioner Refleksi Diri: Kepribadian, Kebiasaan, dan Preferensi',
                'result_label' => 'Profil refleksi diri',
                'description' => 'Pernyataan netral untuk membantu percakapan refleksi tentang kebiasaan dan preferensi sehari-hari; bukan tes kepribadian atau diagnosis.',
                'disclaimer' => 'Kuesioner ini bukan tes kepribadian terstandar, diagnosis, atau penentu tipe/label seseorang. Jawaban merupakan gambaran diri pada saat mengisi; kebiasaan dan preferensi dapat berubah menurut situasi dan pengalaman.',
                'reflection_prompt' => 'Pilih satu kebiasaan yang ingin dipahami atau dikembangkan. Gunakan hasil sebagai awal percakapan dengan Guru BK, bukan sebagai kesimpulan tentang diri atau orang lain.',
                'aspects' => [
                    ['label' => 'Interaksi dan komunikasi', 'questions' => [
                        'Saya nyaman menyampaikan pendapat dalam percakapan kelompok.',
                        'Saya lebih mudah berbicara setelah mendengarkan orang lain terlebih dahulu.',
                        'Saya berusaha menyampaikan ketidaksetujuan dengan cara yang sopan.',
                        'Saya dapat meminta bantuan atau penjelasan ketika membutuhkannya.',
                    ]],
                    ['label' => 'Kemandirian dan tanggung jawab', 'questions' => [
                        'Saya berusaha menyelesaikan tugas sesuai waktu yang disepakati.',
                        'Saya senang menyiapkan hal yang diperlukan sebelum mulai bekerja.',
                        'Saya dapat memulai tugas tanpa selalu menunggu diingatkan.',
                        'Saya berusaha memperbaiki pekerjaan setelah menerima masukan.',
                    ]],
                    ['label' => 'Keterbukaan dan kreativitas', 'questions' => [
                        'Saya tertarik mencoba kegiatan atau cara belajar yang baru.',
                        'Saya senang memikirkan lebih dari satu cara untuk menyelesaikan tugas.',
                        'Saya bersedia mempertimbangkan ide yang berbeda dari kebiasaan saya.',
                        'Saya menikmati mempelajari topik yang belum banyak saya ketahui.',
                    ]],
                    ['label' => 'Pertimbangan dan ketekunan', 'questions' => [
                        'Saya mencoba mengumpulkan informasi sebelum membuat pilihan.',
                        'Saya dapat membagi tugas besar menjadi beberapa langkah kecil.',
                        'Saya berusaha melanjutkan pekerjaan meskipun perlu beberapa kali mencoba.',
                        'Saya mempertimbangkan akibat pilihan bagi diri sendiri dan orang lain.',
                    ]],
                    ['label' => 'Kesadaran emosi dan refleksi', 'questions' => [
                        'Saya berusaha mengenali perasaan yang muncul dalam suatu keadaan.',
                        'Saya dapat mencari cara menenangkan diri yang sesuai bagi saya.',
                        'Saya mau memikirkan hal yang dapat dipelajari dari pengalaman.',
                        'Saya dapat berbicara kepada orang tepercaya ketika membutuhkan dukungan.',
                    ]],
                ],
            ],
        ];
    }
};
