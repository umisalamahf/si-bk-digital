<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $hariIni = now()->toDateString();

        $totalSiswa = DB::table('siswa')->where('status', 'aktif')->count();
        $perluTindakLanjut = DB::table('poin_siswa_rekap')->where('status_tindak_lanjut', 'perlu_tindak_lanjut')->count();
        $perluPerhatian = DB::table('poin_siswa_rekap')->where('status_tindak_lanjut', 'perlu_perhatian')->count();
        $absenHariIni = DB::table('absensi')->whereDate('tanggal', $hariIni)->count();
        $anekdotBeratBelumDitindak = DB::table('catatan_anekdot')->where('kategori', 'berat')->count();

        // Jadwal terdekat & agenda hari ini hanya untuk admin/guru BK
        // (jadwal bisa memuat nama siswa yang sedang konseling, jadi tidak ditampilkan ke role lain)
        $jadwalTerdekat = collect();
        $agendaHariIni = collect();
        if ($user->isAdmin() || $user->isGuruBk()) {
            $jadwalTerdekat = DB::table('jadwal_layanan as j')
                ->leftJoin('kelas as k', 'k.id', '=', 'j.kelas_id')
                ->leftJoin('siswa as s', 's.id', '=', 'j.siswa_id')
                ->where('j.tanggal', '>=', $hariIni)
                ->orderBy('j.tanggal')->orderBy('j.jam_mulai')
                ->limit(5)
                ->get(['j.tanggal', 'j.jam_mulai', 'j.jam_selesai', 'j.jenis_layanan', 'j.tempat',
                       'k.nama_kelas', 's.nama as nama_siswa']);

            $agendaHariIni = DB::table('kalender_agenda')
                ->where('tanggal_mulai', '<=', $hariIni)
                ->where('tanggal_selesai', '>=', $hariIni)
                ->get();
        }

        return view('dashboard.index', compact(
            'totalSiswa', 'perluTindakLanjut', 'perluPerhatian', 'absenHariIni',
            'anekdotBeratBelumDitindak', 'jadwalTerdekat', 'agendaHariIni'
        ));
    }
}
