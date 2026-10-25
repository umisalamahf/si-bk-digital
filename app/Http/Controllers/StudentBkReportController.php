<?php

namespace App\Http\Controllers;

use App\Models\AsesmenJawaban;
use App\Models\Kelas;
use App\Models\Rpl;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentBkReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:150'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        ]);

        $query = Siswa::query()
            ->with('kelas')
            ->select(['id', 'kelas_id', 'nis', 'nama', 'status'])
            ->orderBy('nama');

        if (!empty($filters['cari'])) {
            $query->where(function ($students) use ($filters) {
                $students->where('nama', 'like', '%' . $filters['cari'] . '%')
                    ->orWhere('nis', 'like', '%' . $filters['cari'] . '%');
            });
        }

        if (!empty($filters['kelas_id'])) {
            $query->where('kelas_id', $filters['kelas_id']);
        }

        return view('laporan.siswa_bk.index', [
            'siswa' => $query->paginate(20)->withQueryString(),
            'kelas' => Kelas::query()->orderBy('nama_kelas')->get(['id', 'nama_kelas']),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Siswa $siswa)
    {
        $siswa->load('kelas');

        $rplQuery = Rpl::query()
            ->with([
                'kelas',
                'pelaksanaan',
                'evaluasi' => fn ($query) => $query->orderBy('tanggal')->orderBy('id'),
            ])
            ->withCount('peserta')
            ->whereHas('peserta', fn ($query) => $query->where('siswa.id', $siswa->id))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($request->user()->isGuruBk()) {
            $rplQuery->where('guru_bk_id', $this->guruBkId($request));
        }

        $layanan = $rplQuery->get();

        $asesmen = AsesmenJawaban::query()
            ->with(['instrumen.jenis', 'hasil'])
            ->where('siswa_id', $siswa->id)
            ->whereHas('instrumen.jenis', fn ($query) => $query->where('kode', '<>', 'SOSIOMETRI'))
            ->orderByDesc('tanggal_isi')
            ->orderByDesc('id')
            ->get();

        return view('laporan.siswa_bk.show', compact('siswa', 'layanan', 'asesmen'));
    }

    private function guruBkId(Request $request): int
    {
        $guruBkId = DB::table('guru_bk')
            ->where('user_id', $request->user()->id)
            ->value('id');

        abort_unless($guruBkId, 403);

        return (int) $guruBkId;
    }
}
