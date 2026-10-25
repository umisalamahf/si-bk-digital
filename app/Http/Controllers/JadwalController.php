<?php
namespace App\Http\Controllers;

use App\Models\JadwalLayanan;
use App\Models\KalenderAgenda;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Satu halaman untuk dua hal: Jadwal Layanan BK (per jam) dan Kalender Agenda BK (per rentang tanggal).
class JadwalController extends Controller
{
    public function index()
    {
        $hariIni = now()->toDateString();

        $jadwal = JadwalLayanan::with(['kelas', 'siswa'])
            ->where('tanggal', '>=', $hariIni)
            ->orderBy('tanggal')->orderBy('jam_mulai')->get();

        $agenda = KalenderAgenda::where('tanggal_selesai', '>=', $hariIni)
            ->orderBy('tanggal_mulai')->get();

        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswa = Siswa::where('status', 'aktif')->orderBy('nama')->get();

        return view('jadwal.index', compact('jadwal', 'agenda', 'kelas', 'siswa'));
    }

    public function storeJadwal(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'jenis_layanan' => ['required', 'string', 'max:100'],
            'tempat' => ['nullable', 'string', 'max:100'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'siswa_id' => ['nullable', 'exists:siswa,id'],
            'keterangan' => ['nullable', 'string'],
        ]);
        $data['guru_bk_id'] = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        JadwalLayanan::create($data);

        return redirect()->route('jadwal.index')->with('status', 'Jadwal layanan berhasil ditambahkan.');
    }

    public function storeAgenda(Request $request)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'deskripsi' => ['nullable', 'string'],
        ]);
        $data['guru_bk_id'] = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        KalenderAgenda::create($data);

        return redirect()->route('jadwal.index')->with('status', 'Agenda berhasil ditambahkan.');
    }
}
