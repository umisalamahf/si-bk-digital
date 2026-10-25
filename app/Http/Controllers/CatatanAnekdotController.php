<?php
namespace App\Http\Controllers;

use App\Models\CatatanAnekdot;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatatanAnekdotController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = CatatanAnekdot::with('siswa')->orderByDesc('tanggal');

        // wali_kelas cuma boleh lihat siswa kelasnya sendiri
        if ($user->isWaliKelas()) {
            $guruWaliId = DB::table('guru_wali')->where('user_id', $user->id)->value('id');
            $query->whereHas('siswa.kelas', fn($q) => $q->where('guru_wali_id', $guruWaliId));
        }

        $catatan = $query->paginate(15);

        // ATURAN PRIVASI (sesuai jawaban Guru BK): wali_kelas hanya lihat TANDA untuk
        // kategori berat, BUKAN isi kejadiannya. Disamarkan di sini, bukan di Blade,
        // supaya data aslinya memang tidak pernah terkirim ke browser wali kelas.
        if ($user->isWaliKelas()) {
            $catatan->getCollection()->transform(function ($c) {
                if ($c->kategori === 'berat') {
                    $c->kejadian = '🔒 Ada catatan kategori berat. Silakan hubungi Guru BK untuk detail.';
                    $c->tindak_lanjut = null;
                }
                return $c;
            });
        }

        return view('anekdot.index', compact('catatan'));
    }

    public function create()
    {
        // Hanya guru_bk yang boleh sampai ke sini (dicek middleware di routes),
        // karena catatan konseling data privat, admin pun tidak boleh buat/lihat isi.
        $siswa = Siswa::orderBy('nama')->get();
        return view('anekdot.form', ['anekdot' => new CatatanAnekdot(), 'siswa' => $siswa]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal' => ['required', 'date'],
            'kejadian' => ['required', 'string'],
            'kategori' => ['required', 'in:ringan,sedang,berat'],
            'tindak_lanjut' => ['nullable', 'string'],
        ]);
        $data['guru_bk_id'] = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');

        // notif_wali diisi OTOMATIS oleh trigger trg_after_insert_catatan_anekdot
        // kalau kategori = berat -- jangan diisi manual di sini.
        CatatanAnekdot::create($data);

        return redirect()->route('anekdot.index')->with('status', 'Catatan anekdot berhasil disimpan.');
    }
}
