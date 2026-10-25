<?php

namespace App\Http\Controllers;

use App\Models\Rpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RplReportController extends Controller
{
    private const REPORTS = [
        'konseling-individual' => ['type' => 'konseling_individual', 'label' => 'Konseling Individual'],
        'konseling-kelompok' => ['type' => 'konseling_kelompok', 'label' => 'Konseling Kelompok'],
        'bimbingan-kelompok' => ['type' => 'kelompok', 'label' => 'Bimbingan Kelompok'],
        'klasikal' => ['type' => 'klasikal', 'label' => 'Layanan Klasikal / Kelas Besar'],
    ];

    public function konselingIndividual(Request $request)
    {
        return $this->index($request, 'konseling-individual');
    }

    public function konselingKelompok(Request $request)
    {
        return $this->index($request, 'konseling-kelompok');
    }

    public function bimbinganKelompok(Request $request)
    {
        return $this->index($request, 'bimbingan-kelompok');
    }

    public function klasikal(Request $request)
    {
        return $this->index($request, 'klasikal');
    }

    public function saveExecution(Request $request, string $jenis, Rpl $rpl)
    {
        abort_unless(isset(self::REPORTS[$jenis]), 404);
        $report = self::REPORTS[$jenis];
        $this->authorizeRpl($rpl, $request);
        abort_unless($rpl->jenis_rpl === $report['type'], 404);

        $participantCount = $rpl->peserta()->count();
        $data = $request->validate([
            'jumlah_hadir' => ['required', 'integer', 'min:0', 'max:' . min($participantCount, 65535)],
            'hasil_pelaksanaan' => ['required', 'string', 'max:10000'],
            'hasil_layanan' => ['nullable', 'string', 'max:10000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:10000'],
        ]);

        $rpl->pelaksanaan()->updateOrCreate([], $data + ['created_by' => $request->user()->id]);

        return back()->with('status', 'Rekap pelaksanaan layanan berhasil disimpan.');
    }

    private function index(Request $request, string $jenis)
    {
        $report = self::REPORTS[$jenis];
        $guruBkId = $this->guruBkId($request);
        $rpl = Rpl::where('guru_bk_id', $guruBkId)
            ->where('jenis_rpl', $report['type'])
            ->with(['kelas', 'peserta', 'pelaksanaan'])
            ->orderByDesc('tanggal')
            ->paginate(15);

        return view('laporan.rpl.index', [
            'rplRows' => $rpl,
            'jenis' => $jenis,
            'report' => $report,
            'reports' => self::REPORTS,
        ]);
    }

    private function authorizeRpl(Rpl $rpl, Request $request): void
    {
        abort_unless((int) $rpl->guru_bk_id === (int) $this->guruBkId($request), 403);
    }

    private function guruBkId(Request $request): int
    {
        $guruBkId = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        abort_unless($guruBkId, 403);

        return (int) $guruBkId;
    }
}
