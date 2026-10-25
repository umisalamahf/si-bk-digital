<?php
namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsensiController extends Controller
{
    // Form pilih kelas + tanggal, nanti submit ke simpanMassal()
    public function pilihKelas(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswa = collect();
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $kelasId = $request->get('kelas_id');

        if ($kelasId) {
            // Ambil siswa + absensi yang SUDAH ada untuk tanggal ini (biar form keisi otomatis kalau mau edit)
            $siswa = \App\Models\Siswa::where('kelas_id', $kelasId)
                ->where('status', 'aktif')
                ->orderBy('nama')
                ->get()
                ->map(function ($s) use ($tanggal) {
                    $absenSekarang = Absensi::where('siswa_id', $s->id)->where('tanggal', $tanggal)->first();
                    $s->status_absen = $absenSekarang->status ?? 'hadir';
                    return $s;
                });
        }

        return view('absensi.index', compact('kelas', 'siswa', 'tanggal', 'kelasId'));
    }

    // TRANSAKSI WAJIB: input absensi 1 kelas sekaligus (banyak baris).
    // Kalau salah satu baris gagal (misal status aneh), SEMUA dibatalkan, tidak ada yang tersimpan setengah.
    public function simpanMassal(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'status' => ['required', 'array', 'min:1'],
            'status.*' => ['required', 'in:hadir,sakit,izin,alpa'],
            'keterangan' => ['sometimes', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $studentIds = array_keys($validated['status']);
            $numericStudentIds = array_filter(
                $studentIds,
                static fn ($studentId) => ctype_digit((string) $studentId)
            );
            $classStudentIds = Siswa::where('kelas_id', $validated['kelas_id'])
                ->whereIn('id', $numericStudentIds)
                ->pluck('id')
                ->map(static fn ($studentId) => (string) $studentId)
                ->all();
            $submittedStudentIds = array_map('strval', $studentIds);
            sort($classStudentIds);
            sort($submittedStudentIds);

            if (count($numericStudentIds) !== count($studentIds) || $classStudentIds !== $submittedStudentIds) {
                throw ValidationException::withMessages([
                    'status' => 'Daftar siswa tidak sesuai dengan kelas yang dipilih. Muat ulang halaman dan coba lagi.',
                ]);
            }

            foreach ($validated['status'] as $siswaId => $status) {
                Absensi::updateOrCreate(
                    ['siswa_id' => $siswaId, 'tanggal' => $validated['tanggal']],
                    ['status' => $status, 'keterangan' => $validated['keterangan'][$siswaId] ?? null]
                );
            }
        });

        return back()->with('status', 'Absensi kelas berhasil disimpan (' . count($validated['status']) . ' siswa).');
    }

    // Pakai procedure sp_rekap_absensi -- BUKTI procedure dipanggil dari Laravel
    public function rekap(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $filter = $request->validate([
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $kelasId = $filter['kelas_id'] ?? null;
        $bulan = $filter['bulan'] ?? now()->month;
        $tahun = $filter['tahun'] ?? now()->year;
        $rekap = collect();

        if ($kelasId) {
            $rekap = collect(DB::select('CALL sp_rekap_absensi(?, ?, ?)', [$kelasId, $bulan, $tahun]));
        }

        return view('absensi.rekap', compact('kelas', 'rekap', 'kelasId', 'bulan', 'tahun'));
    }

    public function cetakRekap(Request $request)
    {
        $filter = $request->validate([
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
        ]);

        $kelas = Kelas::findOrFail($filter['kelas_id']);
        $rekap = collect(DB::select('CALL sp_rekap_absensi(?, ?, ?)', [
            $filter['kelas_id'],
            $filter['bulan'],
            $filter['tahun'],
        ]));
        $sekolah = DB::table('sekolah')->first();

        $pdf = Pdf::loadView('absensi.rekap_pdf', [
            'kelas' => $kelas,
            'rekap' => $rekap,
            'bulan' => $filter['bulan'],
            'tahun' => $filter['tahun'],
            'sekolah' => $sekolah,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Rekap-Absensi-' . Str::slug($kelas->nama_kelas) . '-' . $filter['bulan'] . '-' . $filter['tahun'] . '.pdf');
    }
}
