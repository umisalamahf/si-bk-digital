<?php
namespace App\Http\Controllers;

use App\Models\AsesmenJawaban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AsesmenHasilController extends Controller
{
    public function index(Request $request)
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

        $query = AsesmenJawaban::with(['siswa', 'instrumen', 'hasil'])->orderByDesc('id');
        if (!empty($filter['tanggal_mulai'])) {
            $query->whereDate('tanggal_isi', '>=', $filter['tanggal_mulai']);
        }
        if (!empty($filter['tanggal_selesai'])) {
            $query->whereDate('tanggal_isi', '<=', $filter['tanggal_selesai']);
        }

        $jawaban = $query->paginate(15)->withQueryString();
        $rekapSosiometri = $this->rekapSosiometri($filter);

        return view('asesmen.hasil', compact('jawaban', 'rekapSosiometri'));
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

        $query = AsesmenJawaban::with(['siswa.kelas', 'instrumen.jenis', 'hasil'])->orderBy('tanggal_isi');
        if (!empty($filter['tanggal_mulai'])) {
            $query->whereDate('tanggal_isi', '>=', $filter['tanggal_mulai']);
        }
        if (!empty($filter['tanggal_selesai'])) {
            $query->whereDate('tanggal_isi', '<=', $filter['tanggal_selesai']);
        }

        $jawaban = $query->get();
        $rekapSosiometri = $this->rekapSosiometri($filter);
        $sekolah = DB::table('sekolah')->first();
        $pdf = Pdf::loadView('asesmen.hasil_pdf', [
            'jawaban' => $jawaban,
            'rekapSosiometri' => $rekapSosiometri,
            'sekolah' => $sekolah,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap-Hasil-Asesmen.pdf');
    }

    private function rekapSosiometri(array $filter)
    {
        $query = DB::table('asesmen_jawaban_pilihan as pilihan')
            ->join('asesmen_jawaban as jawaban', 'jawaban.id', '=', 'pilihan.jawaban_id')
            ->join('asesmen_link as link', 'link.id', '=', 'jawaban.asesmen_link_id')
            ->join('asesmen_instrumen as instrumen', 'instrumen.id', '=', 'jawaban.instrumen_id')
            ->join('asesmen_jenis as jenis', 'jenis.id', '=', 'instrumen.asesmen_jenis_id')
            ->join('asesmen_butir as butir', 'butir.id', '=', 'pilihan.butir_id')
            ->join('kelas', 'kelas.id', '=', 'link.kelas_id')
            ->join('siswa as terpilih', 'terpilih.id', '=', 'pilihan.siswa_dipilih_id')
            ->where('jenis.kode', 'SOSIOMETRI')
            ->select([
                'link.id as link_id',
                'kelas.nama_kelas',
                'instrumen.nama_instrumen',
                'butir.pertanyaan',
                'butir.kategori',
                'terpilih.nama as siswa_dipilih',
                DB::raw('COUNT(*) as jumlah_dipilih'),
                DB::raw('SUM(CASE WHEN pilihan.urutan_pilihan = 1 THEN 1 ELSE 0 END) as pilihan_pertama'),
                DB::raw('SUM(CASE WHEN pilihan.urutan_pilihan = 2 THEN 1 ELSE 0 END) as pilihan_kedua'),
                DB::raw('SUM(CASE WHEN pilihan.urutan_pilihan = 3 THEN 1 ELSE 0 END) as pilihan_ketiga'),
            ])
            ->groupBy('link.id', 'kelas.nama_kelas', 'instrumen.nama_instrumen', 'butir.pertanyaan', 'butir.kategori', 'terpilih.nama')
            ->orderBy('link.id')
            ->orderBy('butir.pertanyaan')
            ->orderByDesc('jumlah_dipilih');

        if (!empty($filter['tanggal_mulai'])) {
            $query->whereDate('jawaban.tanggal_isi', '>=', $filter['tanggal_mulai']);
        }
        if (!empty($filter['tanggal_selesai'])) {
            $query->whereDate('jawaban.tanggal_isi', '<=', $filter['tanggal_selesai']);
        }

        return $query->get();
    }
}
