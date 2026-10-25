<?php
namespace App\Http\Controllers;

use App\Models\AsesmenLink;
use App\Models\AsesmenJawaban;
use App\Models\AsesmenJawabanDetail;
use App\Models\AsesmenJawabanPilihan;
use App\Models\AsesmenHasil;
use App\Models\Siswa;
use App\Services\AsesmenScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Controller ini TIDAK butuh login -- siswa akses lewat link/token saja.
// Makanya route-nya di luar middleware 'auth' (lihat routes/web.php).
class AsesmenPublikController extends Controller
{
    // Panjang token yang dibuat procedure sp_generate_link_asesmen (hasil SHA2-256 = 64 karakter)
    private const PANJANG_TOKEN = 64;

    // Satu pintu pemeriksaan link, dipakai ketiga langkah (isi, konfirmasi, simpan).
    // Mengembalikan [link, pesanError]. Kalau pesanError null berarti link aman dipakai.
    private function periksaLink(string $token, array $with = []): array
    {
        if (strlen($token) !== self::PANJANG_TOKEN) {
            return [null, 'Link tidak lengkap (terpotong saat disalin). Minta Guru BK mengirim ulang link utuhnya.'];
        }

        $link = AsesmenLink::with($with)->where('token', $token)->first();
        if (!$link) {
            return [null, 'Link tidak ditemukan. Pastikan link yang dibuka adalah link terbaru dari Guru BK.'];
        }

        return [$link, $link->alasanTidakBerlaku()];
    }

    public function isi(string $token)
    {
        [$link, $pesan] = $this->periksaLink($token, ['instrumen.butir', 'kelas']);
        if ($pesan) {
            return view('asesmen.publik_kadaluarsa', ['pesan' => $pesan]);
        }

        $siswaSekelas = Siswa::where('kelas_id', $link->kelas_id)->where('status', 'aktif')->orderBy('nama')->get();
        return view('asesmen.publik_pilih_nama', compact('link', 'siswaSekelas', 'token'));
    }

    // Langkah 2: konfirmasi identitas pakai tanggal lahir (keputusan dari diskusi sebelumnya)
    public function konfirmasi(Request $request, string $token)
    {
        [$link, $pesan] = $this->periksaLink($token, ['instrumen.butir']);
        if ($pesan) {
            return view('asesmen.publik_kadaluarsa', ['pesan' => $pesan]);
        }

        $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_lahir' => ['required', 'date'],
        ]);

        $siswa = Siswa::find($request->siswa_id);
        if (!$siswa || $siswa->kelas_id != $link->kelas_id ||
            $siswa->status !== 'aktif' ||
            (string) $siswa->tanggal_lahir !== (string) $request->tanggal_lahir) {
            return back()->withErrors(['identitas' => 'Nama dan tanggal lahir tidak cocok. Coba lagi.']);
        }

        // fn_cek_sudah_isi_asesmen -- cegah isi 2 kali
        $sudahIsi = DB::selectOne('SELECT fn_cek_sudah_isi_asesmen(?, ?) AS sudah', [$siswa->id, $link->instrumen_id])->sudah;
        if ($sudahIsi) {
            session()->forget($this->sessionKey($token));
            return view('asesmen.publik_sudah_isi', compact('siswa'));
        }

        session()->put($this->sessionKey($token), $siswa->id);

        return redirect()->route('asesmen.publik.form', $token);
    }

    public function form(string $token)
    {
        [$link, $pesan] = $this->periksaLink($token, ['instrumen.butir']);
        if ($pesan) {
            return view('asesmen.publik_kadaluarsa', ['pesan' => $pesan]);
        }

        $siswa = $this->siswaTerkonfirmasi($link, $token);
        if (!$siswa) {
            session()->forget($this->sessionKey($token));
            return redirect()->route('asesmen.publik.isi', $token)
                ->withErrors(['identitas' => 'Silakan konfirmasi nama dan tanggal lahir sebelum mengisi asesmen.']);
        }

        if (DB::selectOne('SELECT fn_cek_sudah_isi_asesmen(?, ?) AS sudah', [$siswa->id, $link->instrumen_id])->sudah) {
            session()->forget($this->sessionKey($token));
            return view('asesmen.publik_sudah_isi', compact('siswa'));
        }

        $siswaPilihan = $link->instrumen->jenis->kode === 'SOSIOMETRI'
            ? Siswa::where('kelas_id', $link->kelas_id)
                ->where('status', 'aktif')
                ->where('id', '!=', $siswa->id)
                ->orderBy('nama')
                ->get()
            : collect();
        $konfigurasiSosiometri = $link->instrumen->konfigurasi_skor ?? [];
        $butirPilihan = $link->instrumen->jenis->kode === 'SOSIOMETRI'
            ? $link->instrumen->butir->whereIn('nomor', $konfigurasiSosiometri['choice_items'] ?? $link->instrumen->butir->pluck('nomor')->all())
            : collect();
        $butirTerbuka = $link->instrumen->jenis->kode === 'SOSIOMETRI'
            ? $link->instrumen->butir->whereIn('nomor', $konfigurasiSosiometri['open_items'] ?? [])
            : collect();
        $responsTerbuka = $link->instrumen->jenis->kode === 'DCM'
            ? ($link->instrumen->konfigurasi_skor['open_responses'] ?? [])
            : [];

        return view('asesmen.publik_form', compact(
            'link',
            'siswa',
            'token',
            'siswaPilihan',
            'butirPilihan',
            'butirTerbuka',
            'responsTerbuka'
        ));
    }

    // TRANSAKSI WAJIB: simpan jawaban (banyak baris) + hitung hasil, SEKALIGUS.
    // Kalau salah satu baris jawaban gagal, SEMUA dibatalkan -- tidak ada jawaban "separuh tersimpan".
    public function simpan(Request $request, string $token)
    {
        [$link, $pesan] = $this->periksaLink($token, ['instrumen.butir']);
        if ($pesan) {
            return view('asesmen.publik_kadaluarsa', ['pesan' => $pesan]);
        }

        $siswa = $this->siswaTerkonfirmasi($link, $token);
        if (!$siswa) {
            session()->forget($this->sessionKey($token));
            return redirect()->route('asesmen.publik.isi', $token)
                ->withErrors(['identitas' => 'Silakan konfirmasi nama dan tanggal lahir sebelum mengisi asesmen.']);
        }

        $jenisKode = $link->instrumen->jenis->kode;
        if ($jenisKode === 'SOSIOMETRI') {
            return $this->simpanSosiometri($request, $link, $siswa, $token);
        }

        $konfigurasiSkor = $link->instrumen->konfigurasi_skor;
        $nilaiJawaban = $jenisKode === 'DCM'
            ? ['required', 'integer', 'in:0,1']
            : (!empty($konfigurasiSkor['response_options'])
                ? ['required', 'integer', 'in:1,2,3,4']
                : ['required', 'integer', 'min:1', 'max:5']);

        $rules = [
            'jawaban' => ['required', 'array'],
            'jawaban.*' => $nilaiJawaban,
            'respons_teks' => ['nullable', 'array'],
            'respons_teks.*' => ['nullable', 'string', 'max:5000'],
        ];
        $openResponses = $konfigurasiSkor['open_responses'] ?? [];
        if ($openResponses !== []) {
            $rules['respons_teks'] = ['required', 'array'];
            foreach (array_keys($openResponses) as $key) {
                $rules["respons_teks.{$key}"] = ['required', 'string', 'max:5000'];
            }
        }
        $request->validate($rules);

        $butirIds = $link->instrumen->butir->pluck('id')->map(fn ($id) => (string) $id)->sort()->values();
        $jawabanIds = collect(array_keys($request->input('jawaban', [])))->map(fn ($id) => (string) $id)->sort()->values();
        if ($butirIds->isEmpty() || $butirIds->all() !== $jawabanIds->all()) {
            return back()->withErrors(['jawaban' => 'Semua butir instrumen wajib dijawab dengan data yang valid.']);
        }

        $sudahIsi = DB::selectOne('SELECT fn_cek_sudah_isi_asesmen(?, ?) AS sudah', [$siswa->id, $link->instrumen_id])->sudah;
        if ($sudahIsi) {
            session()->forget($this->sessionKey($token));
            return view('asesmen.publik_sudah_isi', compact('siswa'));
        }

        $responsTeks = $request->input('respons_teks', []);
        if (!empty($konfigurasiSkor['response_options'])) {
            $hasilSkoring = app(AsesmenScoringService::class)->score(
                $link->instrumen,
                $request->input('jawaban')
            );

            DB::transaction(function () use ($request, $link, $siswa, $hasilSkoring, $responsTeks) {
                $jawaban = AsesmenJawaban::create([
                    'siswa_id' => $siswa->id,
                    'instrumen_id' => $link->instrumen_id,
                    'asesmen_link_id' => $link->id,
                    'respons_teks' => $responsTeks === [] ? null : $responsTeks,
                    'tanggal_isi' => now(),
                ]);

                foreach ($request->jawaban as $butirId => $nilai) {
                    AsesmenJawabanDetail::create([
                        'jawaban_id' => $jawaban->id,
                        'butir_id' => $butirId,
                        'nilai_jawaban' => $nilai,
                    ]);
                }

                AsesmenHasil::create([
                    'jawaban_id' => $jawaban->id,
                    'kategori_dominan_1' => $hasilSkoring['category'],
                    'skor_total' => $hasilSkoring['mode'] === 'profile' ? null : $hasilSkoring['score_total'],
                    'kesimpulan' => $hasilSkoring['conclusion'],
                    'rekomendasi' => $hasilSkoring['recommendation'],
                    'detail_skor' => [
                        'mode' => $hasilSkoring['mode'],
                        'aspek' => $hasilSkoring['aspects'],
                        'aspek_prioritas' => $hasilSkoring['lowest_aspects'],
                        'aspek_disorot' => $hasilSkoring['highlighted_aspects'],
                        'interpretasi_aspek' => $hasilSkoring['aspect_interpretations'],
                        'disclaimer' => $hasilSkoring['disclaimer'],
                    ],
                ]);
            });
        } else {
            DB::transaction(function () use ($request, $link, $siswa, $responsTeks) {
            $jawaban = AsesmenJawaban::create([
                'siswa_id' => $siswa->id,
                'instrumen_id' => $link->instrumen_id,
                'asesmen_link_id' => $link->id,
                'respons_teks' => $responsTeks === [] ? null : $responsTeks,
                'tanggal_isi' => now(),
            ]);

            foreach ($request->jawaban as $butirId => $nilai) {
                AsesmenJawabanDetail::create([
                    'jawaban_id' => $jawaban->id,
                    'butir_id' => $butirId,
                    'nilai_jawaban' => $nilai,
                ]);
            }

            // Panggil PROCEDURE sp_hitung_hasil_asesmen -- skoring terjadi di database
            DB::statement('CALL sp_hitung_hasil_asesmen(?)', [$jawaban->id]);
            });
        }

        session()->forget($this->sessionKey($token));

        return view('asesmen.publik_selesai');
    }

    private function simpanSosiometri(Request $request, AsesmenLink $link, Siswa $siswa, string $token)
    {
        $configuration = $link->instrumen->konfigurasi_skor ?? [];
        $choiceItems = $link->instrumen->butir->whereIn(
            'nomor',
            $configuration['choice_items'] ?? $link->instrumen->butir->pluck('nomor')->all()
        );
        $openItems = $link->instrumen->butir->whereIn('nomor', $configuration['open_items'] ?? []);
        $classmateCount = Siswa::where('kelas_id', $link->kelas_id)
            ->where('status', 'aktif')
            ->where('id', '!=', $siswa->id)
            ->count();
        $hasChoiceLimit = isset($configuration['choices_per_question']);
        $requiredChoices = $hasChoiceLimit
            ? min((int) $configuration['choices_per_question'], $classmateCount)
            : 1;

        $rules = [
            'pilihan' => ['required', 'array'],
            'pilihan.*' => $hasChoiceLimit
                ? ['required', 'array', 'min:' . $requiredChoices, 'max:' . $requiredChoices]
                : ['required', 'array', 'min:1'],
            'pilihan.*.*' => [
                'required',
                'integer',
                Rule::exists('siswa', 'id')->where(fn ($query) => $query
                    ->where('kelas_id', $link->kelas_id)
                    ->where('status', 'aktif')),
            ],
        ];
        if ($openItems->isNotEmpty()) {
            $rules['respons_teks'] = ['required', 'array'];
            $rules['respons_teks.*'] = ['required', 'string', 'max:5000'];
        }
        $request->validate($rules);

        $butirIds = $choiceItems->pluck('id')->map(fn ($id) => (string) $id)->sort()->values();
        $pilihanIds = collect(array_keys($request->input('pilihan', [])))->map(fn ($id) => (string) $id)->sort()->values();
        $responsKeys = collect(array_keys($request->input('respons_teks', [])))
            ->map(fn ($number) => (int) $number)->sort()->values();
        $openNumbers = $openItems->pluck('nomor')->map(fn ($number) => (int) $number)->sort()->values();
        if ($butirIds->isEmpty() || $butirIds->all() !== $pilihanIds->all() ||
            $openNumbers->all() !== $responsKeys->all() || $requiredChoices < 1) {
            return back()->withErrors(['pilihan' => 'Setiap butir sosiometri harus memiliki pilihan yang valid.']);
        }

        foreach ($request->input('pilihan', []) as $butirId => $siswaTerpilih) {
            if (count(array_unique($siswaTerpilih)) !== count($siswaTerpilih) ||
                ($hasChoiceLimit && count($siswaTerpilih) !== $requiredChoices)) {
                return back()->withErrors([
                    'pilihan' => $hasChoiceLimit
                        ? "Pilih {$requiredChoices} teman berbeda untuk setiap pertanyaan sosiometri."
                        : 'Setiap pilihan sosiometri harus berisi nama teman yang berbeda.',
                ])->withInput();
            }
            if (in_array((int) $siswa->id, array_map('intval', $siswaTerpilih), true)) {
                return back()->withErrors(['pilihan' => 'Siswa tidak dapat memilih dirinya sendiri.']);
            }
        }

        $sudahIsi = DB::selectOne('SELECT fn_cek_sudah_isi_asesmen(?, ?) AS sudah', [$siswa->id, $link->instrumen_id])->sudah;
        if ($sudahIsi) {
            session()->forget($this->sessionKey($token));
            return view('asesmen.publik_sudah_isi', compact('siswa'));
        }

        DB::transaction(function () use ($request, $link, $siswa, $hasChoiceLimit) {
            $jawaban = AsesmenJawaban::create([
                'siswa_id' => $siswa->id,
                'instrumen_id' => $link->instrumen_id,
                'asesmen_link_id' => $link->id,
                'respons_teks' => $request->input('respons_teks'),
                'tanggal_isi' => now(),
            ]);

            foreach ($request->input('pilihan') as $butirId => $siswaTerpilih) {
                foreach (array_values($siswaTerpilih) as $index => $siswaDipilihId) {
                    AsesmenJawabanPilihan::create([
                        'jawaban_id' => $jawaban->id,
                        'butir_id' => $butirId,
                        'siswa_dipilih_id' => $siswaDipilihId,
                        'urutan_pilihan' => $hasChoiceLimit ? $index + 1 : null,
                    ]);
                }
            }

            AsesmenHasil::create([
                'jawaban_id' => $jawaban->id,
                'kesimpulan' => 'Pilihan sosiometri tersimpan. Tinjau rekap pemilihan kelompok sebagai bahan asesmen.',
                'rekomendasi' => 'Interpretasikan pola pilihan sesuai pedoman sosiometri yang ditetapkan Guru BK.',
            ]);
        });

        session()->forget($this->sessionKey($token));

        return view('asesmen.publik_selesai');
    }

    private function siswaTerkonfirmasi(AsesmenLink $link, string $token): ?Siswa
    {
        return Siswa::whereKey(session()->get($this->sessionKey($token)))
            ->where('kelas_id', $link->kelas_id)
            ->where('status', 'aktif')
            ->first();
    }

    private function sessionKey(string $token): string
    {
        return 'asesmen.siswa_terkonfirmasi.' . $token;
    }
}
