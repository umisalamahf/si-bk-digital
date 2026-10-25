<?php
namespace App\Http\Controllers;

use App\Models\ProgramBk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramBkController extends Controller
{
    private const JENIS_PROGRAM = ['tahunan', 'semester_ganjil', 'semester_genap'];

    public function tahunan(Request $request)
    {
        [$program, $tahunAjaran] = $this->programUntuk($request, 'tahunan');

        return view('program.tahunan', compact('program', 'tahunAjaran'));
    }

    public function semester(Request $request)
    {
        $jenisProgram = $request->query('jenis_program', 'semester_ganjil');
        abort_unless(in_array($jenisProgram, ['semester_ganjil', 'semester_genap'], true), 404);
        [$program, $tahunAjaran] = $this->programUntuk($request, $jenisProgram);

        return view('program.semester', compact('program', 'tahunAjaran', 'jenisProgram'));
    }

    public function updateTahunan(Request $request, ProgramBk $program)
    {
        abort_unless($program->jenis_program === 'tahunan', 404);
        $this->pastikanMilikGuru($request, $program);
        $this->simpan($request, $program);

        return redirect()->route('program-tahunan.index', [
            'tahun_ajaran' => $program->tahun_ajaran,
        ])->with('status', 'Program Tahunan berhasil disimpan.');
    }

    public function updateSemester(Request $request, ProgramBk $program)
    {
        abort_unless(in_array($program->jenis_program, ['semester_ganjil', 'semester_genap'], true), 404);
        $this->pastikanMilikGuru($request, $program);
        $this->simpan($request, $program);

        return redirect()->route('program-semester.index', [
            'jenis_program' => $program->jenis_program,
            'tahun_ajaran' => $program->tahun_ajaran,
        ])->with('status', 'Program Semester berhasil disimpan.');
    }

    public function legacyIndex(Request $request)
    {
        $jenisProgram = $request->query('jenis_program', 'tahunan');
        if (in_array($jenisProgram, ['semester_ganjil', 'semester_genap'], true)) {
            return redirect()->route('program-semester.index', $request->only('jenis_program', 'tahun_ajaran'));
        }

        return redirect()->route('program-tahunan.index', $request->only('tahun_ajaran'));
    }

    public function legacyUpdate(Request $request, ProgramBk $program)
    {
        if ($program->jenis_program === 'tahunan') {
            return $this->updateTahunan($request, $program);
        }

        return $this->updateSemester($request, $program);
    }

    private function programUntuk(Request $request, string $jenisProgram): array
    {
        $tahunAjaran = $request->query('tahun_ajaran', $this->tahunAjaranBerjalan());
        $guruBkId = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');

        $program = ProgramBk::firstOrCreate(
            [
                'guru_bk_id' => $guruBkId,
                'tahun_ajaran' => $tahunAjaran,
                'jenis_program' => $jenisProgram,
            ],
            ['capaian_layanan' => '', 'alur_tujuan' => '', 'action_plan' => '']
        );

        return [$program, $tahunAjaran];
    }

    private function simpan(Request $request, ProgramBk $program): void
    {
        $data = $request->validate([
            'capaian_layanan' => ['nullable', 'string'],
            'alur_tujuan' => ['nullable', 'string'],
            'action_plan' => ['nullable', 'string'],
        ]);
        $program->update($data);
    }

    private function pastikanMilikGuru(Request $request, ProgramBk $program): void
    {
        $guruBkId = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        abort_unless((int) $program->guru_bk_id === (int) $guruBkId, 403);
    }

    private function tahunAjaranBerjalan(): string
    {
        $bulan = now()->month;
        $tahun = now()->year;
        // Tahun ajaran baru mulai Juli (bulan 7)
        return $bulan >= 7 ? "$tahun/" . ($tahun + 1) : ($tahun - 1) . "/$tahun";
    }
}
