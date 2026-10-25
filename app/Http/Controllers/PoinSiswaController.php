<?php
namespace App\Http\Controllers;

use App\Models\PoinSiswa;
use App\Models\PoinSiswaRekap;
use App\Models\Siswa;
use App\Models\JenisPelanggaran;
use App\Models\JenisReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PoinSiswaController extends Controller
{
    public function index(Request $request)
    {
        // Rekap dibaca langsung dari poin_siswa_rekap -- tabel ini DIISI OTOMATIS
        // oleh trigger, bukan dihitung ulang manual di sini.
        $query = PoinSiswaRekap::with('siswa')->orderByDesc('total_bersih');
        if ($request->filled('status')) {
            $query->where('status_tindak_lanjut', $request->status);
        }
        $rekap = $query->paginate(15);
        $aturanAkumulasi = DB::table('aturan_akumulasi_poin')->orderBy('poin_min')->get();
        return view('poin.index', compact('rekap', 'aturanAkumulasi'));
    }

    public function cetakRekap(Request $request)
    {
        $filter = $request->validate([
            'status' => ['nullable', 'in:aman,perlu_perhatian,perlu_tindak_lanjut'],
        ]);

        $query = PoinSiswaRekap::with(['siswa.kelas'])->orderByDesc('total_bersih');
        if (!empty($filter['status'])) {
            $query->where('status_tindak_lanjut', $filter['status']);
        }

        $rekap = $query->get();
        $sekolah = DB::table('sekolah')->first();

        $pdf = Pdf::loadView('poin.rekap_pdf', [
            'rekap' => $rekap,
            'status' => $filter['status'] ?? null,
            'sekolah' => $sekolah,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap-Poin-Siswa.pdf');
    }

    public function cetakDetail(Request $request)
    {
        $filter = $request->validate([
            'tipe' => ['required', 'in:pelanggaran,reward'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('tanggal_mulai') && $value < $request->input('tanggal_mulai')) {
                        $fail('Tanggal selesai harus sama dengan atau setelah tanggal mulai.');
                    }
                },
            ],
        ]);

        $query = PoinSiswa::with(['siswa.kelas', 'jenisPelanggaran', 'jenisReward'])
            ->where('tipe', $filter['tipe'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');
        if (!empty($filter['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filter['tanggal_mulai']);
        }
        if (!empty($filter['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $filter['tanggal_selesai']);
        }

        $poin = $query->get();
        $sekolah = DB::table('sekolah')->first();
        $pdf = Pdf::loadView('poin.detail_pdf', [
            'poin' => $poin,
            'tipe' => $filter['tipe'],
            'sekolah' => $sekolah,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
            'filter' => $filter,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap-' . ucfirst($filter['tipe']) . '-Siswa.pdf');
    }

    public function create()
    {
        $siswa = Siswa::orderBy('nama')->get();
        $pelanggaran = JenisPelanggaran::orderBy('nama_pelanggaran')->get();
        $reward = JenisReward::orderBy('nama_reward')->get();
        return view('poin.form', compact('siswa', 'pelanggaran', 'reward'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tipe' => ['required', 'in:pelanggaran,reward'],
            'jenis_pelanggaran_id' => ['required_if:tipe,pelanggaran', 'nullable', 'exists:jenis_pelanggaran,id'],
            'jenis_reward_id' => ['required_if:tipe,reward', 'nullable', 'exists:jenis_reward,id'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        // Poin DIAMBIL OTOMATIS dari master (bukan diketik manual guru), biar tidak salah angka
        if ($data['tipe'] === 'pelanggaran') {
            $data['poin'] = JenisPelanggaran::findOrFail($data['jenis_pelanggaran_id'])->poin;
        } else {
            $data['poin'] = JenisReward::findOrFail($data['jenis_reward_id'])->poin;
        }

        $data['guru_bk_id'] = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        // Tahun ajaran diambil dari tahun ajaran kelas siswa saat ini (karena rekap direset per tahun ajaran)
        $data['tahun_ajaran'] = Siswa::find($data['siswa_id'])->kelas->tahun_ajaran;

        // Insert ini otomatis memicu trigger trg_after_insert_poin_siswa,
        // yang langsung memanggil procedure sp_rekap_poin_siswa.
        // Tidak perlu DB::transaction() manual di sini karena cuma 1 tabel yang disentuh
        // dari sisi Laravel -- triggernya yang urus tabel rekap di level database.
        PoinSiswa::create($data);

        return redirect()->route('poin.index')->with('status', 'Poin berhasil dicatat, rekap otomatis diperbarui.');
    }

    public function riwayat(Siswa $siswa)
    {
        $riwayat = PoinSiswa::with(['jenisPelanggaran', 'jenisReward'])
            ->where('siswa_id', $siswa->id)->orderByDesc('tanggal')->get();
        $rekap = PoinSiswaRekap::where('siswa_id', $siswa->id)->get();
        return view('poin.riwayat', compact('siswa', 'riwayat', 'rekap'));
    }

    // Form ubah poin. Tipe (pelanggaran/reward) dan siswanya TIDAK bisa diubah dari sini;
    // kalau salah siswa atau salah tipe, hapus lalu catat ulang (supaya riwayatnya tetap jujur).
    public function edit(PoinSiswa $poin)
    {
        $poin->load('siswa.kelas');
        $pelanggaran = JenisPelanggaran::orderBy('nama_pelanggaran')->get();
        $reward = JenisReward::orderBy('nama_reward')->get();
        return view('poin.edit', compact('poin', 'pelanggaran', 'reward'));
    }

    public function update(Request $request, PoinSiswa $poin)
    {
        $data = $request->validate([
            'jenis_pelanggaran_id' => [$poin->tipe === 'pelanggaran' ? 'required' : 'nullable', 'exists:jenis_pelanggaran,id'],
            'jenis_reward_id' => [$poin->tipe === 'reward' ? 'required' : 'nullable', 'exists:jenis_reward,id'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        // Poin diambil ulang dari master sesuai jenis yang dipilih (sama seperti saat mencatat baru)
        if ($poin->tipe === 'pelanggaran') {
            $poin->jenis_pelanggaran_id = $data['jenis_pelanggaran_id'];
            $poin->poin = JenisPelanggaran::findOrFail($data['jenis_pelanggaran_id'])->poin;
        } else {
            $poin->jenis_reward_id = $data['jenis_reward_id'];
            $poin->poin = JenisReward::findOrFail($data['jenis_reward_id'])->poin;
        }
        $poin->tanggal = $data['tanggal'];
        $poin->keterangan = $data['keterangan'] ?? null;

        // save() = UPDATE di database -> memicu trigger trg_after_update_poin_siswa,
        // yang menghitung ulang poin_siswa_rekap secara otomatis.
        $poin->save();

        return redirect()->route('poin.riwayat', $poin->siswa_id)
            ->with('status', 'Poin berhasil diubah, rekap otomatis diperbarui.');
    }

    public function destroy(PoinSiswa $poin)
    {
        $siswaId = $poin->siswa_id;

        // delete() = DELETE di database -> memicu trigger trg_after_delete_poin_siswa,
        // yang menghitung ulang rekap (status "perlu tindak lanjut" bisa turun lagi).
        $poin->delete();

        return redirect()->route('poin.riwayat', $siswaId)
            ->with('status', 'Poin berhasil dihapus, rekap otomatis diperbarui.');
    }
}
