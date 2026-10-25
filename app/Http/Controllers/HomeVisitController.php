<?php
namespace App\Http\Controllers;

use App\Models\HomeVisit;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

// Home visit = catatan privat tentang keluarga siswa -> hanya guru_bk (lihat routes/web.php).
class HomeVisitController extends Controller
{
    public function index()
    {
        $kunjungan = HomeVisit::with('siswa')->orderByDesc('tanggal')->paginate(15);
        $siswa = Siswa::where('status', 'aktif')->orderBy('nama')->get();
        return view('homevisit.index', compact('kunjungan', 'siswa'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal' => ['required', 'date'],
            'alamat_dikunjungi' => ['nullable', 'string', 'max:255'],
            'pihak_ditemui' => ['nullable', 'string', 'max:255'],
            'tujuan' => ['required', 'string', 'max:255'],
            'hasil' => ['nullable', 'string'],
            'tindak_lanjut' => ['nullable', 'string'],
        ]);
        $data['guru_bk_id'] = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        HomeVisit::create($data);

        return redirect()->route('homevisit.index')->with('status', 'Catatan home visit berhasil disimpan.');
    }

    public function show(HomeVisit $homeVisit)
    {
        $homeVisit->load('siswa.kelas');
        return view('homevisit.show', compact('homeVisit'));
    }

    public function cetak(HomeVisit $homeVisit)
    {
        $homeVisit->load('siswa.kelas');
        $sekolah = DB::table('sekolah')->first();
        $guruBk = DB::table('guru_bk')->where('id', $homeVisit->guru_bk_id)->first();

        $pdf = Pdf::loadView('homevisit.pdf', compact('homeVisit', 'sekolah', 'guruBk'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Home-Visit-' . $homeVisit->id . '.pdf');
    }

    public function cetakRekap(Request $request)
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

        $query = HomeVisit::with('siswa.kelas')->orderByDesc('tanggal');
        if (!empty($filter['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filter['tanggal_mulai']);
        }
        if (!empty($filter['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $filter['tanggal_selesai']);
        }

        $kunjungan = $query->get();
        $sekolah = DB::table('sekolah')->first();
        $pdf = Pdf::loadView('homevisit.rekap_pdf', [
            'kunjungan' => $kunjungan,
            'sekolah' => $sekolah,
            'filter' => $filter,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap-Home-Visit.pdf');
    }
}
