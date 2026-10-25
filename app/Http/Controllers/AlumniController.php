<?php
namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Kelas;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumni::with('siswa.kelas')->orderByDesc('tahun_lulus')->orderBy('id');
        if ($request->filled('tahun_lulus')) {
            $query->where('tahun_lulus', $request->tahun_lulus);
        }
        $alumni = $query->paginate(20)->withQueryString();

        $kelasLulus = Kelas::where('tingkat', 9)->orderBy('nama_kelas')->get();
        $kelasAsal = Kelas::where('tingkat', '<', 9)->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $kelasTujuan = Kelas::where('tingkat', '>', 7)->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $daftarTahun = Alumni::select('tahun_lulus')->distinct()->orderByDesc('tahun_lulus')->pluck('tahun_lulus');

        return view('alumni.index', compact('alumni', 'kelasLulus', 'kelasAsal', 'kelasTujuan', 'daftarTahun'));
    }

    public function cetak(Request $request)
    {
        $filter = $request->validate([
            'tahun_lulus' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $query = Alumni::with('siswa.kelas')->orderByDesc('tahun_lulus')->orderBy('id');
        if (!empty($filter['tahun_lulus'])) {
            $query->where('tahun_lulus', $filter['tahun_lulus']);
        }

        $sekolah = DB::table('sekolah')->first();
        $alumni = $query->get();
        $pdf = Pdf::loadView('alumni.pdf', [
            'alumni' => $alumni,
            'tahunLulus' => $filter['tahun_lulus'] ?? null,
            'sekolah' => $sekolah,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Rekap-Alumni.pdf');
    }

    // Memanggil PROCEDURE sp_kelulusan_kelas. Procedure ini mengatur transaksinya SENDIRI
    // (START TRANSACTION ... COMMIT / ROLLBACK), makanya TIDAK dibungkus DB::transaction() di sini:
    // transaksi di dalam transaksi akan membuat database meng-COMMIT yang luar secara diam-diam.
    public function lulus(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tahun_lulus' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        try {
            DB::statement('CALL sp_kelulusan_kelas(?, ?, @jumlah)', [$data['kelas_id'], $data['tahun_lulus']]);
            $jumlah = DB::selectOne('SELECT @jumlah AS jumlah')->jumlah;
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->pesanDatabase($e));
        }

        return redirect()->route('alumni.index')
            ->with('status', "$jumlah siswa berhasil diluluskan dan tercatat sebagai alumni tahun {$data['tahun_lulus']}.");
    }

    public function naik(Request $request)
    {
        $data = $request->validate([
            'kelas_asal' => ['required', 'exists:kelas,id'],
            'kelas_tujuan' => ['required', 'exists:kelas,id', 'different:kelas_asal'],
        ]);

        try {
            DB::statement('CALL sp_kenaikan_kelas(?, ?, @jumlah)', [$data['kelas_asal'], $data['kelas_tujuan']]);
            $jumlah = DB::selectOne('SELECT @jumlah AS jumlah')->jumlah;
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->pesanDatabase($e));
        }

        return redirect()->route('alumni.index')->with('status', "$jumlah siswa berhasil dinaikkan ke kelas tujuan.");
    }

    // Ambil kalimat pesan dari SIGNAL di procedure (errorInfo[2]), bukan seluruh teks teknis SQL
    private function pesanDatabase(QueryException $e): string
    {
        return $e->errorInfo[2] ?? 'Terjadi kesalahan pada database.';
    }
}
