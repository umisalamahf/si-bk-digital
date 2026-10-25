<?php
namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SuratController extends Controller
{
    public function index()
    {
        $surat = Surat::with(['jenisSurat', 'siswa'])->orderByDesc('id')->paginate(15);
        return view('surat.index', compact('surat'));
    }

    public function create(Request $request)
    {
        $jenisSurat = JenisSurat::where('kode', '!=', 'SKB')->orderBy('nama_surat')->get();
        $siswa = Siswa::orderBy('nama')->get();
        $jenisTerpilih = $request->query('jenis') === 'SPO'
            ? $jenisSurat->firstWhere('kode', 'SPO')?->id
            : null;

        return view('surat.form', compact('jenisSurat', 'siswa', 'jenisTerpilih'));
    }

    public function createKelakuanBaik()
    {
        $siswa = Siswa::with('kelas')->where('status', 'aktif')->orderBy('nama')->get();

        return view('surat.kelakuan_baik_form', compact('siswa'));
    }

    // TRANSAKSI: generate nomor + simpan surat HARUS satu paket.
    // Kalau simpan surat gagal setelah nomor "dipakai", nomor itu akan bolong (dilewati) --
    // itu wajar & aman (bukan bentrok), yang penting tidak ada 2 surat dengan nomor SAMA.
    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis_surat_id' => ['required', 'exists:jenis_surat,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal' => ['required', 'date'],
            'perihal' => ['nullable', 'string', 'max:200'],
            'isi' => ['nullable', 'string'],
        ]);
        if (JenisSurat::whereKey($data['jenis_surat_id'])->where('kode', 'SKB')->exists()) {
            return back()->withInput()->withErrors([
                'jenis_surat_id' => 'Surat Kelakuan Baik harus dibuat melalui formulir khusus Guru BK.',
            ]);
        }

        DB::transaction(function () use ($data, $request) {
            // Panggil PROCEDURE sp_generate_nomor_surat -- pakai SELECT...FOR UPDATE di dalamnya,
            // jadi aman walau 2 surat jenis sama dibuat PERSIS bersamaan.
            DB::statement('CALL sp_generate_nomor_surat(?, @nomor)', [$data['jenis_surat_id']]);
            $nomor = DB::select('SELECT @nomor AS nomor')[0]->nomor;

            Surat::create([
                ...$data,
                'nomor_surat' => $nomor,
                'guru_bk_id' => DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id'),
                'status' => 'draft',
            ]);
        });

        return redirect()->route('surat.index')->with('status', 'Surat berhasil dibuat dengan nomor otomatis.');
    }

    public function storeKelakuanBaik(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:200'],
        ]);
        if (!Siswa::whereKey($data['siswa_id'])->where('status', 'aktif')->exists()) {
            return back()->withInput()->withErrors([
                'siswa_id' => 'Surat hanya dapat dibuat untuk siswa yang berstatus aktif.',
            ]);
        }

        $jenisSurat = JenisSurat::where('kode', 'SKB')->firstOrFail();
        $guruBkId = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        if (!$guruBkId) {
            abort(422, 'Profil Guru BK untuk akun ini belum tersedia.');
        }

        $surat = DB::transaction(function () use ($data, $jenisSurat, $guruBkId) {
            DB::statement('CALL sp_generate_nomor_surat(?, @nomor)', [$jenisSurat->id]);
            $nomor = DB::select('SELECT @nomor AS nomor')[0]->nomor;

            return Surat::create([
                'jenis_surat_id' => $jenisSurat->id,
                'siswa_id' => $data['siswa_id'],
                'guru_bk_id' => $guruBkId,
                'nomor_surat' => $nomor,
                'tanggal' => $data['tanggal'],
                'perihal' => $data['perihal'],
                'isi' => null,
                'status' => 'draft',
            ]);
        });

        return redirect()->route('surat.cetak', $surat)
            ->with('status', 'Surat Kelakuan Baik berhasil dibuat dan siap dicetak.');
    }

    // Cetak surat sebagai PDF A4 asli (library barryvdh/laravel-dompdf).
    // Urutannya sengaja begini: PDF dibuat DULU (->output()), baru status diubah jadi 'dicetak'.
    // Jadi kalau pembuatan PDF gagal, status surat tidak salah tercatat "sudah dicetak".
    public function cetak(Surat $surat)
    {
        $surat->load(['jenisSurat', 'siswa.kelas']);

        $sekolah = DB::table('sekolah')->first();
        $guruBk = DB::table('guru_bk')->where('id', $surat->guru_bk_id)->first();
        // Nama bulan Indonesia ikut APP_LOCALE=id yang sudah diatur di .env
        $tanggalTampil = Carbon::parse($surat->tanggal)->translatedFormat('d F Y');

        $pdf = Pdf::loadView('surat.pdf', compact('surat', 'sekolah', 'guruBk', 'tanggalTampil'))
            ->setPaper('a4', 'portrait');
        $isi = $pdf->output();

        $surat->update(['status' => 'dicetak']);

        // Nomor surat memuat tanda "/" yang tidak boleh ada di nama file
        $namaFile = 'Surat-' . str_replace('/', '-', $surat->nomor_surat) . '.pdf';

        return response($isi, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $namaFile . '"',
        ]);
    }
}
