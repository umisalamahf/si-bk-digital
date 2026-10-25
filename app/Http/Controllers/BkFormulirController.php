<?php

namespace App\Http\Controllers;

use App\Models\BkFormulir;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BkFormulirController extends Controller
{
    private const RATING_OPTIONS = ['SL', 'SR', 'KD', 'TP'];

    public function index()
    {
        $formulir = BkFormulir::with(['siswa.kelas', 'siswaPihakKedua.kelas', 'pembuat'])
            ->latest('tanggal')
            ->latest('id')
            ->paginate(20);

        return view('bk-formulir.index', compact('formulir'));
    }

    public function create(string $jenis)
    {
        $definition = $this->definition($jenis);
        $siswa = Siswa::where('status', 'aktif')->with('kelas')->orderBy('nama')->get();

        return view('bk-formulir.form', compact('definition', 'jenis', 'siswa'));
    }

    public function store(Request $request, string $jenis)
    {
        $definition = $this->definition($jenis);
        $rules = [
            'tanggal' => ['required', 'date'],
            'siswa_id' => [
                'required',
                'integer',
                Rule::exists('siswa', 'id')->where(fn ($query) => $query->where('status', 'aktif')),
            ],
        ];

        if ($jenis === 'mediasi') {
            $rules['siswa_pihak_2_id'] = [
                'required',
                'integer',
                'different:siswa_id',
                Rule::exists('siswa', 'id')->where(fn ($query) => $query->where('status', 'aktif')),
            ];
        }

        foreach ($definition['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $key = 'data.' . $field['name'];
                $required = $field['required'] ?? false;

                if ($field['type'] === 'ratings') {
                    $rules[$key] = ['required', 'array'];
                    $rules[$key . '.*'] = ['required', 'in:' . implode(',', self::RATING_OPTIONS)];
                    continue;
                }

                if ($field['type'] === 'date') {
                    $rules[$key] = [$required ? 'required' : 'nullable', 'date'];
                    continue;
                }

                if ($field['type'] === 'checkbox') {
                    $rules[$key] = [$required ? 'required' : 'nullable', 'array'];
                    $rules[$key . '.*'] = ['string', 'in:' . implode(',', array_keys($field['options']))];
                    continue;
                }

                $fieldRules = [$required ? 'required' : 'nullable'];
                if ($field['type'] === 'select') {
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'in:' . implode(',', array_keys($field['options']));
                } else {
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:' . ($field['max'] ?? 5000);
                }
                $rules[$key] = $fieldRules;
            }
        }

        $validated = $request->validate($rules);
        $data = $validated['data'] ?? [];

        foreach ($definition['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                if ($field['type'] !== 'ratings') {
                    continue;
                }

                $expected = array_keys($field['items']);
                $actual = array_keys($data[$field['name']] ?? []);
                sort($expected);
                sort($actual);
                if ($expected !== $actual) {
                    throw ValidationException::withMessages([
                        'data.' . $field['name'] => 'Semua aspek observasi wajib diberi penilaian.',
                    ]);
                }
            }
        }

        BkFormulir::create([
            'jenis' => $jenis,
            'siswa_id' => $validated['siswa_id'],
            'siswa_pihak_2_id' => $validated['siswa_pihak_2_id'] ?? null,
            'user_id' => $request->user()->id,
            'tanggal' => $validated['tanggal'],
            'data' => $data,
        ]);

        return redirect()->route('bk-formulir.index')
            ->with('status', $definition['title'] . ' berhasil disimpan.');
    }

    public function show(BkFormulir $formulir)
    {
        $formulir->load(['siswa.kelas', 'siswaPihakKedua.kelas', 'pembuat']);
        $definition = $this->definition($formulir->jenis);

        return view('bk-formulir.show', compact('formulir', 'definition'));
    }

    private function definition(string $jenis): array
    {
        $definitions = [
            'observasi' => $this->observasiDefinition(),
            'wawancara' => $this->wawancaraDefinition(),
            'konsultasi' => $this->konsultasiDefinition(),
            'mediasi' => $this->mediasiDefinition(),
        ];

        abort_unless(isset($definitions[$jenis]), 404);

        return $definitions[$jenis];
    }

    private function observasiDefinition(): array
    {
        return [
            'title' => 'Lembar Observasi Murid',
            'sections' => [
                [
                    'title' => 'Informasi Observasi',
                    'fields' => [
                        $this->text('observer', 'Observer', true, 150),
                        $this->text('tempat_kegiatan', 'Tempat/Kegiatan', true, 200),
                    ],
                ],
                [
                    'title' => 'Penilaian Aspek yang Diamati',
                    'fields' => [[
                        'name' => 'ratings',
                        'label' => 'Pilih frekuensi yang sesuai untuk setiap aspek.',
                        'type' => 'ratings',
                        'required' => true,
                        'items' => [
                            1 => 'Datang ke sekolah/kegiatan tepat waktu.',
                            2 => 'Menunjukkan sikap percaya diri.',
                            3 => 'Mampu mengendalikan emosi.',
                            4 => 'Mampu mengikuti aturan yang berlaku.',
                            5 => 'Menunjukkan tanggung jawab terhadap tugas.',
                            6 => 'Memperhatikan penjelasan guru.',
                            7 => 'Aktif bertanya atau menjawab pertanyaan.',
                            8 => 'Mengerjakan tugas yang diberikan.',
                            9 => 'Mampu berkonsentrasi selama kegiatan belajar.',
                            10 => 'Menunjukkan ketekunan ketika menghadapi kesulitan belajar.',
                            11 => 'Mau berinteraksi dengan teman.',
                            12 => 'Mampu bekerja sama dalam kelompok.',
                            13 => 'Menghargai pendapat teman.',
                            14 => 'Bersedia membantu teman yang mengalami kesulitan.',
                            15 => 'Mampu menyelesaikan konflik dengan cara yang baik.',
                            16 => 'Berkomunikasi dengan sopan kepada guru.',
                            17 => 'Berkomunikasi dengan baik kepada teman.',
                            18 => 'Mampu menyampaikan pendapat dengan jelas.',
                            19 => 'Mau mendengarkan ketika orang lain berbicara.',
                            20 => 'Berani mengungkapkan kesulitan atau kebutuhan kepada guru.',
                            21 => 'Mampu menyesuaikan diri dengan lingkungan sekolah.',
                            22 => 'Mampu mengikuti perubahan atau situasi baru.',
                            23 => 'Tampak nyaman berada di lingkungan kelas.',
                            24 => 'Mampu menerima kritik dan saran.',
                            25 => 'Menunjukkan kemandirian dalam menyelesaikan masalah sederhana.',
                            26 => 'Menunjukkan minat terhadap kegiatan atau bidang tertentu.',
                            27 => 'Menunjukkan usaha untuk mengembangkan kemampuan diri.',
                            28 => 'Memiliki gambaran mengenai cita-cita atau masa depan.',
                            29 => 'Menunjukkan kesungguhan dalam mencapai tujuan belajar.',
                            30 => 'Aktif mencari informasi tentang pendidikan atau karier.',
                        ],
                    ]],
                ],
                [
                    'title' => 'Catatan dan Tindak Lanjut',
                    'fields' => [
                        $this->textarea('perilaku_positif', 'Perilaku positif yang terlihat'),
                        $this->textarea('perilaku_perhatian', 'Perilaku yang perlu mendapatkan perhatian'),
                        $this->textarea('situasi_pengaruh', 'Situasi/kondisi yang memengaruhi perilaku murid'),
                        $this->textarea('hal_tindak_lanjut', 'Hal yang perlu ditindaklanjuti'),
                        $this->textarea('kesimpulan', 'Kesimpulan observasi'),
                        $this->checkbox('rencana_tindak_lanjut', 'Rencana tindak lanjut', [
                            'Tidak diperlukan tindak lanjut khusus' => 'Tidak diperlukan tindak lanjut khusus',
                            'Pemantauan perkembangan murid' => 'Pemantauan perkembangan murid',
                            'Layanan BK klasikal' => 'Layanan BK klasikal',
                            'Bimbingan kelompok' => 'Bimbingan kelompok',
                            'Konseling individual' => 'Konseling individual',
                            'Kolaborasi dengan wali kelas/guru' => 'Kolaborasi dengan wali kelas/guru',
                            'Komunikasi dengan orang tua/wali' => 'Komunikasi dengan orang tua/wali',
                            'Rujukan sesuai kebutuhan' => 'Rujukan sesuai kebutuhan',
                        ]),
                    ],
                ],
            ],
        ];
    }

    private function wawancaraDefinition(): array
    {
        return [
            'title' => 'Pedoman Wawancara Guru BK',
            'sections' => [
                [
                    'title' => 'Identitas Wawancara',
                    'fields' => [
                        $this->text('pewawancara', 'Nama pewawancara/Guru BK', true, 150),
                        $this->text('tempat', 'Tempat', false, 200),
                    ],
                ],
                [
                    'title' => 'Pembukaan',
                    'fields' => [
                        $this->checkbox('langkah_pembukaan', 'Panduan awal wawancara', [
                            'Menyapa murid dengan ramah dan menciptakan suasana nyaman' => 'Menyapa murid dengan ramah dan menciptakan suasana nyaman',
                            'Menjelaskan tujuan wawancara' => 'Menjelaskan tujuan wawancara',
                            'Menjelaskan bahwa murid dapat menyampaikan keadaan dengan jujur' => 'Menjelaskan bahwa murid dapat menyampaikan keadaan dengan jujur',
                            'Menjaga kerahasiaan informasi sesuai ketentuan layanan BK' => 'Menjaga kerahasiaan informasi sesuai ketentuan layanan BK',
                            'Menghindari sikap menghakimi atau menyalahkan murid' => 'Menghindari sikap menghakimi atau menyalahkan murid',
                        ]),
                    ],
                ],
                [
                    'title' => 'Kondisi Pribadi',
                    'fields' => $this->questionFields('pribadi', [
                        'Bagaimana perasaan kamu akhir-akhir ini?',
                        'Apa kegiatan yang paling kamu sukai?',
                        'Menurut kamu, apa kelebihan yang kamu miliki?',
                        'Apa hal dalam dirimu yang ingin kamu perbaiki?',
                        'Apa yang biasanya kamu lakukan ketika sedang menghadapi masalah?',
                        'Apakah ada hal yang akhir-akhir ini membuat kamu merasa tidak nyaman atau tertekan?',
                        'Siapa orang yang biasanya kamu percaya untuk bercerita ketika memiliki masalah?',
                    ]),
                ],
                [
                    'title' => 'Kondisi Belajar',
                    'fields' => $this->questionFields('belajar', [
                        'Bagaimana kegiatan belajar kamu di sekolah?',
                        'Mata pelajaran apa yang paling kamu sukai? Mengapa?',
                        'Mata pelajaran apa yang paling sulit bagi kamu?',
                        'Bagaimana kebiasaan belajar kamu di rumah?',
                        'Apa yang biasanya membuat kamu sulit berkonsentrasi?',
                        'Apakah kamu mengalami kesulitan mengerjakan atau mengumpulkan tugas?',
                        'Apa yang kamu lakukan ketika tidak memahami pelajaran?',
                        'Apa yang dapat dilakukan Guru BK untuk membantu kamu dalam belajar?',
                    ]),
                ],
                [
                    'title' => 'Kondisi Sosial dan Pertemanan',
                    'fields' => $this->questionFields('sosial', [
                        'Bagaimana hubungan kamu dengan teman-teman di kelas?',
                        'Apakah kamu memiliki teman dekat?',
                        'Apakah kamu merasa nyaman berada di lingkungan kelas?',
                        'Apakah pernah terjadi konflik atau masalah dengan teman?',
                        'Bagaimana biasanya kamu menyelesaikan masalah dengan teman?',
                        'Apakah kamu pernah merasa dikucilkan atau tidak diterima dalam kelompok?',
                        'Apakah kamu merasa mudah berkenalan dengan teman baru?',
                    ]),
                ],
                [
                    'title' => 'Kondisi Keluarga',
                    'fields' => $this->questionFields('keluarga', [
                        'Bagaimana hubungan kamu dengan orang tua atau wali?',
                        'Apakah kamu merasa nyaman bercerita kepada keluarga?',
                        'Bagaimana keluarga mendukung kegiatan belajar kamu?',
                        'Apakah ada aturan di rumah yang menurut kamu sulit untuk dijalankan?',
                        'Apakah ada masalah di rumah yang memengaruhi kegiatan belajar atau kehidupan kamu di sekolah?',
                        'Apa yang kamu harapkan dari keluarga?',
                    ]),
                ],
                [
                    'title' => 'Penyesuaian Diri di Sekolah',
                    'fields' => $this->questionFields('penyesuaian', [
                        'Bagaimana perasaan kamu selama berada di sekolah?',
                        'Apakah kamu merasa nyaman dengan guru dan teman-teman?',
                        'Apakah kamu mengalami kesulitan mengikuti aturan sekolah?',
                        'Apakah kamu mengalami kesulitan menyesuaikan diri dengan lingkungan atau situasi baru?',
                        'Apa yang biasanya kamu lakukan ketika menghadapi perubahan?',
                        'Hal apa di sekolah yang paling membuat kamu nyaman?',
                        'Hal apa di sekolah yang paling ingin kamu ubah atau perbaiki?',
                    ]),
                ],
                [
                    'title' => 'Pendidikan dan Karier',
                    'fields' => $this->questionFields('karier', [
                        'Apa cita-cita atau pekerjaan yang kamu inginkan saat ini?',
                        'Apa alasan kamu memilih cita-cita tersebut?',
                        'Setelah lulus SMP, kamu ingin melanjutkan ke sekolah atau pendidikan seperti apa?',
                        'Apakah kamu sudah memiliki pilihan SMA/SMK atau jurusan tertentu?',
                        'Apa yang kamu ketahui tentang pilihan tersebut?',
                        'Apa yang membuat kamu yakin atau ragu dengan pilihanmu?',
                        'Apakah kamu membutuhkan informasi tentang sekolah lanjutan atau pekerjaan?',
                        'Siapa yang paling sering membantu kamu dalam menentukan rencana masa depan?',
                    ]),
                ],
                [
                    'title' => 'Identifikasi dan Tindak Lanjut',
                    'fields' => [
                        $this->checkbox('masalah_utama', 'Masalah utama yang membutuhkan bantuan', [
                            'Pribadi' => 'Pribadi', 'Belajar' => 'Belajar', 'Sosial/pertemanan' => 'Sosial/pertemanan',
                            'Keluarga' => 'Keluarga', 'Penyesuaian diri' => 'Penyesuaian diri',
                            'Pendidikan lanjutan' => 'Pendidikan lanjutan', 'Karier' => 'Karier', 'Lainnya' => 'Lainnya',
                        ]),
                        $this->textarea('uraian_masalah', 'Uraian masalah'),
                        $this->textarea('harapan_murid', 'Jawaban/harapan murid'),
                        $this->textarea('hasil_identifikasi', 'Hasil identifikasi Guru BK'),
                        $this->textarea('kebutuhan_murid', 'Kebutuhan murid'),
                        $this->checkbox('rencana_tindak_lanjut', 'Rencana tindak lanjut', [
                            'Pemantauan' => 'Pemantauan', 'Layanan informasi' => 'Layanan informasi',
                            'Bimbingan klasikal' => 'Bimbingan klasikal', 'Bimbingan kelompok' => 'Bimbingan kelompok',
                            'Konseling individual' => 'Konseling individual', 'Konsultasi dengan orang tua/wali' => 'Konsultasi dengan orang tua/wali',
                            'Kolaborasi dengan wali kelas/guru mata pelajaran' => 'Kolaborasi dengan wali kelas/guru mata pelajaran',
                            'Rujukan sesuai kebutuhan' => 'Rujukan sesuai kebutuhan',
                        ]),
                        $this->textarea('catatan_guru_bk', 'Catatan Guru BK'),
                    ],
                ],
            ],
        ];
    }

    private function konsultasiDefinition(): array
    {
        return [
            'title' => 'Formulir Konsultasi',
            'sections' => [
                [
                    'title' => 'Identitas',
                    'fields' => [
                        $this->text('nama_pihak_berkonsultasi', 'Nama pihak yang berkonsultasi', false, 150),
                        $this->checkbox('pihak_berkonsultasi', 'Pihak yang berkonsultasi', [
                            'Murid' => 'Murid', 'Orang tua/Wali' => 'Orang tua/Wali', 'Wali kelas' => 'Wali kelas',
                            'Guru mata pelajaran' => 'Guru mata pelajaran', 'Lainnya' => 'Lainnya',
                        ], true),
                        $this->text('nama_konsultan', 'Nama konsultan/Guru BK', true, 150),
                    ],
                ],
                [
                    'title' => 'Tujuan Konsultasi',
                    'fields' => [
                        $this->checkbox('tujuan', 'Konsultasi berkaitan dengan', [
                            'Masalah pribadi' => 'Masalah pribadi', 'Masalah belajar' => 'Masalah belajar',
                            'Masalah sosial/pertemanan' => 'Masalah sosial/pertemanan', 'Masalah keluarga' => 'Masalah keluarga',
                            'Penyesuaian diri' => 'Penyesuaian diri', 'Kedisiplinan/perilaku' => 'Kedisiplinan/perilaku',
                            'Pendidikan lanjutan' => 'Pendidikan lanjutan', 'Perencanaan karier' => 'Perencanaan karier',
                            'Perkembangan murid' => 'Perkembangan murid', 'Lainnya' => 'Lainnya',
                        ], true),
                    ],
                ],
                [
                    'title' => 'Uraian dan Hasil',
                    'fields' => [
                        $this->textarea('masalah_kebutuhan', 'Masalah atau kebutuhan yang dikonsultasikan', true),
                        $this->textarea('awal_masalah', 'Kapan masalah mulai terlihat/terjadi?'),
                        $this->textarea('situasi_berkaitan', 'Situasi yang berkaitan dengan masalah'),
                        $this->textarea('informasi', 'Hasil penggalian informasi'),
                        $this->textarea('faktor', 'Faktor yang diduga memengaruhi'),
                        $this->textarea('hasil_pembahasan', 'Hasil pembahasan'),
                        $this->textarea('saran', 'Saran/alternatif solusi'),
                        $this->textarea('kesepakatan', 'Kesepakatan yang dibuat'),
                    ],
                ],
                [
                    'title' => 'Tindak Lanjut',
                    'fields' => [
                        $this->checkbox('rencana_tindak_lanjut', 'Rencana tindak lanjut', [
                            'Pemantauan perkembangan murid' => 'Pemantauan perkembangan murid',
                            'Layanan informasi' => 'Layanan informasi', 'Bimbingan klasikal' => 'Bimbingan klasikal',
                            'Bimbingan kelompok' => 'Bimbingan kelompok', 'Konseling individual' => 'Konseling individual',
                            'Pendampingan belajar' => 'Pendampingan belajar', 'Kolaborasi dengan wali kelas' => 'Kolaborasi dengan wali kelas',
                            'Kolaborasi dengan guru mata pelajaran' => 'Kolaborasi dengan guru mata pelajaran',
                            'Komunikasi dengan orang tua/wali' => 'Komunikasi dengan orang tua/wali',
                            'Rujukan kepada pihak yang sesuai' => 'Rujukan kepada pihak yang sesuai',
                        ]),
                        $this->text('waktu_tindak_lanjut', 'Waktu tindak lanjut', false, 150),
                        $this->text('penanggung_jawab', 'Penanggung jawab', false, 150),
                        $this->date('tanggal_pemantauan', 'Tanggal pemantauan'),
                        $this->textarea('hasil_pemantauan', 'Hasil pemantauan'),
                        $this->textarea('perkembangan_murid', 'Perkembangan/kondisi murid'),
                        $this->textarea('tindak_lanjut_berikutnya', 'Tindak lanjut berikutnya'),
                    ],
                ],
            ],
        ];
    }

    private function mediasiDefinition(): array
    {
        return [
            'title' => 'Formulir Mediasi Konflik Siswa',
            'sections' => [
                [
                    'title' => 'Identitas Mediasi',
                    'fields' => [
                        $this->text('waktu', 'Waktu mediasi', false, 100),
                        $this->text('tempat', 'Tempat', true, 200),
                        $this->text('mediator', 'Guru BK/mediator', true, 150),
                        $this->text('pihak_lain', 'Pihak lain yang terlibat (jika ada)', false, 500),
                    ],
                ],
                [
                    'title' => 'Latar Belakang Masalah',
                    'fields' => [
                        $this->checkbox('jenis_konflik', 'Jenis konflik', [
                            'Kesalahpahaman' => 'Kesalahpahaman', 'Perselisihan dengan teman' => 'Perselisihan dengan teman',
                            'Perbedaan pendapat' => 'Perbedaan pendapat', 'Masalah komunikasi' => 'Masalah komunikasi',
                            'Masalah kerja kelompok' => 'Masalah kerja kelompok', 'Masalah pertemanan' => 'Masalah pertemanan',
                            'Pelanggaran kesepakatan' => 'Pelanggaran kesepakatan', 'Lainnya' => 'Lainnya',
                        ], true),
                        $this->text('jenis_konflik_lainnya', 'Jenis konflik lainnya (jika memilih Lainnya)', false, 200),
                        $this->textarea('uraian_masalah', 'Uraian singkat masalah', true),
                        $this->textarea('waktu_tempat_kejadian', 'Waktu dan tempat kejadian'),
                    ],
                ],
                [
                    'title' => 'Pandangan Para Pihak',
                    'fields' => [
                        $this->textarea('pandangan_pihak_1', 'Menurut Pihak I, apa yang terjadi?'),
                        $this->textarea('perasaan_pihak_1', 'Perasaan Pihak I terhadap masalah'),
                        $this->textarea('harapan_pihak_1', 'Harapan Pihak I terhadap penyelesaian'),
                        $this->textarea('pandangan_pihak_2', 'Menurut Pihak II, apa yang terjadi?'),
                        $this->textarea('perasaan_pihak_2', 'Perasaan Pihak II terhadap masalah'),
                        $this->textarea('harapan_pihak_2', 'Harapan Pihak II terhadap penyelesaian'),
                    ],
                ],
                [
                    'title' => 'Hasil Mediasi dan Kesepakatan',
                    'fields' => [
                        $this->textarea('pokok_masalah', 'Pokok masalah yang berhasil diidentifikasi'),
                        $this->textarea('kesamaan_pemahaman', 'Kesamaan pemahaman kedua pihak'),
                        $this->textarea('perbedaan', 'Hal yang masih menjadi perbedaan'),
                        $this->textarea('kesepakatan', 'Kesepakatan bersama', true),
                        $this->textarea('komitmen_pihak_1', 'Komitmen Pihak I'),
                        $this->textarea('komitmen_pihak_2', 'Komitmen Pihak II'),
                        $this->text('batas_waktu', 'Batas waktu pelaksanaan kesepakatan', false, 150),
                    ],
                ],
                [
                    'title' => 'Tindak Lanjut dan Hasil Akhir',
                    'fields' => [
                        $this->checkbox('rencana_tindak_lanjut', 'Rencana tindak lanjut', [
                            'Tidak diperlukan tindak lanjut khusus' => 'Tidak diperlukan tindak lanjut khusus',
                            'Pemantauan oleh guru BK' => 'Pemantauan oleh guru BK',
                            'Pemantauan oleh wali kelas' => 'Pemantauan oleh wali kelas',
                            'Konseling individual Pihak I' => 'Konseling individual Pihak I',
                            'Konseling individual Pihak II' => 'Konseling individual Pihak II',
                            'Bimbingan kelompok' => 'Bimbingan kelompok',
                            'Konsultasi dengan orang tua/wali' => 'Konsultasi dengan orang tua/wali',
                            'Kolaborasi dengan guru/wali kelas' => 'Kolaborasi dengan guru/wali kelas',
                        ]),
                        $this->date('tanggal_pemantauan', 'Tanggal pemantauan berikutnya'),
                        $this->textarea('hasil_pemantauan', 'Hasil pemantauan'),
                        $this->select('hasil_akhir', 'Hasil akhir mediasi', [
                            'Konflik terselesaikan' => 'Konflik terselesaikan',
                            'Sebagian konflik terselesaikan' => 'Sebagian konflik terselesaikan',
                            'Membutuhkan mediasi lanjutan' => 'Membutuhkan mediasi lanjutan',
                            'Membutuhkan layanan BK lanjutan' => 'Membutuhkan layanan BK lanjutan',
                            'Membutuhkan penanganan/rujukan sesuai kondisi' => 'Membutuhkan penanganan/rujukan sesuai kondisi',
                        ]),
                        $this->textarea('catatan_mediator', 'Catatan Guru BK/Mediator'),
                        $this->text('nama_pihak_1', 'Nama Pihak I untuk kesepakatan', false, 150),
                        $this->text('nama_pihak_2', 'Nama Pihak II untuk kesepakatan', false, 150),
                        $this->text('wali_orang_tua', 'Nama wali kelas/orang tua/wali (jika mengetahui)', false, 150),
                    ],
                ],
            ],
        ];
    }

    private function questionFields(string $prefix, array $questions): array
    {
        return array_map(
            fn (string $question, int $index) => $this->textarea(
                "{$prefix}_" . ($index + 1),
                $question
            ),
            $questions,
            array_keys($questions)
        );
    }

    private function text(string $name, string $label, bool $required = false, int $max = 5000): array
    {
        return compact('name', 'label', 'required', 'max') + ['type' => 'text'];
    }

    private function textarea(string $name, string $label, bool $required = false): array
    {
        return compact('name', 'label', 'required') + ['type' => 'textarea'];
    }

    private function date(string $name, string $label, bool $required = false): array
    {
        return compact('name', 'label', 'required') + ['type' => 'date'];
    }

    private function checkbox(string $name, string $label, array $options, bool $required = false): array
    {
        return compact('name', 'label', 'options', 'required') + ['type' => 'checkbox'];
    }

    private function select(string $name, string $label, array $options, bool $required = true): array
    {
        return compact('name', 'label', 'options', 'required') + ['type' => 'select'];
    }
}
