<?php
namespace App\Http\Controllers;

use App\Models\JurnalLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class JurnalLayananController extends Controller
{
    public function index()
    {
        $jurnal = JurnalLayanan::orderByDesc('tanggal')->paginate(15);
        return view('jurnal.index', compact('jurnal'));
    }

    public function bulanan(Request $request)
    {
        $filter = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $bulan = (int) ($filter['bulan'] ?? now()->month);
        $tahun = (int) ($filter['tahun'] ?? now()->year);

        $jurnal = JurnalLayanan::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal')
            ->paginate(25)
            ->withQueryString();
        $jumlahKegiatan = JurnalLayanan::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->count();
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');

        return view('jurnal.bulanan', compact('jurnal', 'bulan', 'tahun', 'namaBulan', 'jumlahKegiatan'));
    }

    public function cetakBulanan(Request $request)
    {
        $filter = $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
        ]);
        $periode = Carbon::createFromDate((int) $filter['tahun'], (int) $filter['bulan'], 1);
        $tanggalMulai = $periode->copy()->startOfMonth()->toDateString();
        $tanggalSelesai = $periode->copy()->endOfMonth()->toDateString();
        $jurnal = JurnalLayanan::whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('tanggal')
            ->get();
        $sekolah = DB::table('sekolah')->first();
        $filter['tanggal_mulai'] = $tanggalMulai;
        $filter['tanggal_selesai'] = $tanggalSelesai;
        $judul = 'JURNAL BULANAN GURU BIMBINGAN DAN KONSELING';
        $labelPeriode = $periode->translatedFormat('F Y');
        $pdf = Pdf::loadView('jurnal.pdf', [
            'jurnal' => $jurnal,
            'sekolah' => $sekolah,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
            'filter' => $filter,
            'judul' => $judul,
            'labelPeriode' => $labelPeriode,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Jurnal-Bulanan-BK-' . $periode->format('Y-m') . '.pdf');
    }

    public function cetak(Request $request)
    {
        $filter = $request->validate([
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

        $query = JurnalLayanan::orderBy('tanggal');
        if (!empty($filter['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filter['tanggal_mulai']);
        }
        if (!empty($filter['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $filter['tanggal_selesai']);
        }

        $jurnal = $query->get();
        $sekolah = DB::table('sekolah')->first();
        $pdf = Pdf::loadView('jurnal.pdf', [
            'jurnal' => $jurnal,
            'sekolah' => $sekolah,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
            'filter' => $filter,
            'judul' => 'JURNAL HARIAN GURU BIMBINGAN DAN KONSELING',
            'labelPeriode' => null,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap-Jurnal-BK.pdf');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'kegiatan' => ['required', 'string', 'max:255'],
            'sasaran' => ['nullable', 'string', 'max:200'],
            'uraian' => ['required', 'string', 'max:10000'],
        ]);
        $data['guru_bk_id'] = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        JurnalLayanan::create($data);

        return back()->with('status', 'Jurnal harian berhasil dicatat.');
    }
}
