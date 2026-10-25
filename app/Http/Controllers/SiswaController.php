<?php
namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Http\Requests\StoreSiswaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SiswaController extends Controller
{
    // Daftar siswa: cari & filter kelas, paginasi (cegah query berat kalau siswa banyak)
    public function index(Request $request)
    {
        // Kolom 'usia' dihitung oleh FUNCTION database fn_hitung_usia (bukan dihitung di PHP)
        $query = Siswa::with('kelas')
            ->selectRaw('siswa.*, fn_hitung_usia(siswa.tanggal_lahir) as usia')
            ->orderBy('nama');

        // wali_kelas HANYA boleh lihat siswa di kelas yang dia bimbing
        if ($request->user()->isWaliKelas()) {
            $guruWaliId = \DB::table('guru_wali')->where('user_id', $request->user()->id)->value('id');
            $query->whereHas('kelas', fn($q) => $q->where('guru_wali_id', $guruWaliId));
        }

        if ($request->filled('cari')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->cari . '%')
                  ->orWhere('nis', 'like', '%' . $request->cari . '%');
            });
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswa = $query->paginate(15)->withQueryString();
        $kelas = Kelas::whereHas('siswa', fn ($siswa) => $siswa->where('status', 'aktif'))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('siswa.index', compact('siswa', 'kelas'));
    }

    public function cetak(Request $request)
    {
        $filter = $request->validate([
            'cari' => ['nullable', 'string', 'max:150'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        ]);

        $query = Siswa::with('kelas')
            ->selectRaw('siswa.*, fn_hitung_usia(siswa.tanggal_lahir) as usia')
            ->orderBy('nama');

        if ($request->user()->isWaliKelas()) {
            $guruWaliId = DB::table('guru_wali')->where('user_id', $request->user()->id)->value('id');
            $query->whereHas('kelas', fn ($kelas) => $kelas->where('guru_wali_id', $guruWaliId));
        }
        if (!empty($filter['cari'])) {
            $query->where(function ($siswa) use ($filter) {
                $siswa->where('nama', 'like', '%' . $filter['cari'] . '%')
                    ->orWhere('nis', 'like', '%' . $filter['cari'] . '%');
            });
        }
        if (!empty($filter['kelas_id'])) {
            $query->where('kelas_id', $filter['kelas_id']);
        }

        $siswa = $query->get();
        $sekolah = DB::table('sekolah')->first();
        $pdf = Pdf::loadView('siswa.pdf', [
            'siswa' => $siswa,
            'sekolah' => $sekolah,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap-Data-Siswa.pdf');
    }

    public function show(Siswa $siswa)
    {
        $siswa->load(['kelas', 'user']);
        return view('siswa.show', compact('siswa'));
    }

    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        return view('siswa.form', ['siswa' => new Siswa(), 'kelas' => $kelas]);
    }

    public function store(StoreSiswaRequest $request)
    {
        // Trigger trg_before_insert_siswa ikut jaga-jaga NIS kosong,
        // tapi validasi di FormRequest lebih duluan supaya pesan errornya ramah di Blade.
        Siswa::create($request->validated());
        return redirect()->route('siswa.index')->with('status', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        return view('siswa.form', compact('siswa', 'kelas'));
    }

    public function update(StoreSiswaRequest $request, Siswa $siswa)
    {
        // Update ini yang membuat trigger trg_audit_update_siswa jalan (tercatat di audit_log)
        $siswa->update($request->validated());
        $siswa->user?->update(['name' => $siswa->nama]);
        return redirect()->route('siswa.index')->with('status', 'Data siswa berhasil diubah.');
    }

    public function destroy(Siswa $siswa)
    {
        // Kalau siswa ini sudah punya riwayat (poin/absensi/dll), FK ON DELETE RESTRICT
        // akan menolak hapus -- kita tangkap errornya biar pesannya ramah.
        try {
            $siswa->delete();
            return redirect()->route('siswa.index')->with('status', 'Siswa berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Siswa ini tidak bisa dihapus karena sudah punya riwayat data (poin/absensi/dll). Ubah status jadi "Tidak Aktif" saja.');
        }
    }
}
