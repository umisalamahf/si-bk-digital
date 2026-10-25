<?php
namespace App\Http\Controllers;

use App\Models\Rpl;
use App\Models\RplSiswa;
use App\Models\ProgramBk;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Http\Requests\StoreRplRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RplController extends Controller
{
    public function index(Request $request)
    {
        $rpl = Rpl::with(['kelas', 'peserta'])->orderByDesc('tanggal')->paginate(15);
        return view('rpl.index', compact('rpl'));
    }

    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswa = Siswa::where('status', 'aktif')->orderBy('nama')->get();
        $sekolah = DB::table('sekolah')->first();
        $guruBk = DB::table('guru_bk')->where('user_id', request()->user()->id)->first();
        return view('rpl.form', compact('kelas', 'siswa', 'sekolah', 'guruBk'));
    }

    // TRANSAKSI WAJIB (sesuai spesifikasi bagian 9, poin 1):
    // simpan RPL + peserta (rpl_siswa) HARUS satu paket. Kalau salah satu peserta gagal
    // disimpan (misal FK rusak), RPL induknya ikut dibatalkan -- tidak ada RPL "yatim" tanpa peserta.
    public function store(StoreRplRequest $request)
    {
        $guruBkId = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        $tahunAjaran = $this->tahunAjaranBerjalan();
        $programBk = ProgramBk::firstOrCreate(
            ['guru_bk_id' => $guruBkId, 'tahun_ajaran' => $tahunAjaran],
            ['capaian_layanan' => '', 'alur_tujuan' => '', 'action_plan' => '']
        );

        try {
            DB::transaction(function () use ($request, $guruBkId, $programBk) {
                $data = $request->validated();
                $data['guru_bk_id'] = $guruBkId;
                $data['program_bk_id'] = $programBk->id;
                if ($data['jenis_rpl'] === 'klasikal') {
                    $data['topik'] = $data['topik_layanan'];
                }
                if ($data['jenis_rpl'] === 'konseling_kelompok') {
                    $data['masalah'] = $data['topik'];
                }
                $this->applyRplIdentity($data, $guruBkId);

                // RPL klasikal: peserta OTOMATIS semua siswa aktif di kelas itu (sesuai ERD bag.3)
                if ($data['jenis_rpl'] === 'klasikal') {
                    $pesertaIds = Siswa::where('kelas_id', $data['kelas_id'])->where('status', 'aktif')->pluck('id');
                } else {
                    $pesertaIds = collect($request->input('siswa_ids', []));
                    $data['kelas_id'] = null;
                }

                $rpl = Rpl::create(collect($data)->except('siswa_ids')->toArray());

                foreach ($pesertaIds as $siswaId) {
                    RplSiswa::create(['rpl_id' => $rpl->id, 'siswa_id' => $siswaId]);
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap pesan dari trigger trg_before_insert_rpl (jaring pengaman kedua)
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }

        return redirect()->route('rpl.index')->with('status', 'RPL berhasil disimpan beserta pesertanya.');
    }

    public function edit(Rpl $rpl)
    {
        $rpl->load('peserta');
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $pesertaSaatIni = $rpl->peserta->pluck('id');
        $siswa = Siswa::where('status', 'aktif')
            ->orWhereIn('id', $pesertaSaatIni)
            ->orderBy('nama')
            ->get();
        $sekolah = DB::table('sekolah')->first();
        $guruBk = DB::table('guru_bk')->where('id', $rpl->guru_bk_id)->first();

        return view('rpl.form', compact('rpl', 'kelas', 'siswa', 'sekolah', 'guruBk'));
    }

    public function update(StoreRplRequest $request, Rpl $rpl)
    {
        $data = $request->validated();

        if ($data['jenis_rpl'] === 'klasikal') {
            $data['topik'] = $data['topik_layanan'];
            $pesertaIds = Siswa::where('kelas_id', $data['kelas_id'])
                ->where('status', 'aktif')
                ->pluck('id');
        } else {
            $pesertaIds = collect($data['siswa_ids']);
            $data['kelas_id'] = null;
        }
        if ($data['jenis_rpl'] === 'konseling_kelompok') {
            $data['masalah'] = $data['topik'];
        }

        unset($data['siswa_ids']);
        $this->applyRplIdentity($data, $rpl->guru_bk_id);

        DB::transaction(function () use ($rpl, $data, $pesertaIds) {
            $rpl->update($data);
            $rpl->peserta()->sync($pesertaIds);
        });

        return redirect()->route('rpl.show', $rpl)->with('status', 'RPL berhasil diperbarui beserta pesertanya.');
    }

    public function show(Rpl $rpl)
    {
        $rpl->load(['kelas', 'peserta.kelas', 'evaluasi']);
        return view('rpl.show', compact('rpl'));
    }

    public function cetak(Rpl $rpl)
    {
        $rpl->load(['kelas', 'peserta.kelas', 'evaluasi']);
        $sekolah = DB::table('sekolah')->first();
        $guruBk = DB::table('guru_bk')->where('id', $rpl->guru_bk_id)->first();

        $pdf = Pdf::loadView('rpl.pdf', compact('rpl', 'sekolah', 'guruBk'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('RPL-' . $rpl->id . '.pdf');
    }

    private function applyRplIdentity(array &$data, int $guruBkId): void
    {
        $sekolah = DB::table('sekolah')->first();
        $guruBk = DB::table('guru_bk')->where('id', $guruBkId)->first();
        $kelas = !empty($data['kelas_id'])
            ? Kelas::find($data['kelas_id'])
            : null;

        $data['satuan_pendidikan'] = $sekolah?->nama_sekolah;
        $data['tahun_ajaran'] = $kelas?->tahun_ajaran
            ?? $this->tahunAjaranBerjalan();
        $data['nama_kepala_sekolah'] = $sekolah?->kepala_sekolah;
        $data['nip_kepala_sekolah'] = $sekolah?->kepala_sekolah_nip;
        $data['nama_guru_bk'] = $guruBk?->nama;
        $data['nip_guru_bk'] = $guruBk?->nip;
    }

    private function tahunAjaranBerjalan(): string
    {
        $bulan = now()->month;
        $tahun = now()->year;
        return $bulan >= 7 ? "$tahun/" . ($tahun + 1) : ($tahun - 1) . "/$tahun";
    }
}
