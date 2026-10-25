<?php

namespace App\Http\Controllers;

use App\Models\AngketKepuasan;
use App\Models\ResponKepuasan;
use App\Models\Rpl;
use App\Services\KepuasanScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class KepuasanLayananController extends Controller
{
    public function createLink(Rpl $rpl, Request $request)
    {
        $this->authorizeRpl($rpl, $request);

        $token = Str::random(64);
        AngketKepuasan::create([
            'rpl_id' => $rpl->id,
            'token_hash' => hash('sha256', $token),
            'created_by' => $request->user()->id,
            'expires_at' => now()->addDays(90),
        ]);

        return redirect()
            ->route('rpl.show', $rpl)
            ->with('status', 'Tautan angket kepuasan dibuat dan berlaku selama 90 hari.')
            ->with('survey_url', URL::route('kepuasan.publik.form', ['token' => $token]));
    }

    public function show(string $token)
    {
        $angket = $this->findValidSurvey($token);

        return view('kepuasan.form', [
            'angket' => $angket->load('rpl'),
            'questions' => KepuasanScoringService::QUESTIONS,
            'labels' => KepuasanScoringService::LABELS,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $angket = $this->findValidSurvey($token);
        $data = $request->validate([
            'kategori_responden' => ['required', 'in:peserta_didik,orang_tua,kepala_sekolah,guru'],
            'jawaban' => ['required', 'array:kejelasan,kesesuaian,kenyamanan,manfaat,tindak_lanjut'],
            'jawaban.kejelasan' => ['required', 'integer', 'between:1,4'],
            'jawaban.kesesuaian' => ['required', 'integer', 'between:1,4'],
            'jawaban.kenyamanan' => ['required', 'integer', 'between:1,4'],
            'jawaban.manfaat' => ['required', 'integer', 'between:1,4'],
            'jawaban.tindak_lanjut' => ['required', 'integer', 'between:1,4'],
        ]);

        DB::transaction(fn () => ResponKepuasan::create([
            'angket_kepuasan_id' => $angket->id,
            'kategori_responden' => $data['kategori_responden'],
            'jawaban' => $data['jawaban'],
        ]));

        return view('kepuasan.terkirim');
    }

    public function results(Rpl $rpl, Request $request, KepuasanScoringService $scoring)
    {
        $this->authorizeRpl($rpl, $request);

        $responses = ResponKepuasan::whereHas('angket', fn ($query) => $query->where('rpl_id', $rpl->id))
            ->get();
        $categories = ['peserta_didik', 'orang_tua', 'kepala_sekolah', 'guru'];
        $scoresByCategory = collect($categories)->mapWithKeys(fn ($category) => [
            $category => [
                'count' => $responses->where('kategori_responden', $category)->count(),
                'scores' => $scoring->summarize($responses->where('kategori_responden', $category)->values()),
            ],
        ]);

        return view('kepuasan.hasil', [
            'rpl' => $rpl,
            'responseCount' => $responses->count(),
            'overallScores' => $scoring->summarize($responses),
            'scoresByCategory' => $scoresByCategory,
            'questions' => KepuasanScoringService::QUESTIONS,
            'categoryLabels' => [
                'peserta_didik' => 'Peserta didik',
                'orang_tua' => 'Orang tua',
                'kepala_sekolah' => 'Kepala sekolah',
                'guru' => 'Guru',
            ],
        ]);
    }

    private function findValidSurvey(string $token): AngketKepuasan
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token), 404);

        $angket = AngketKepuasan::where('token_hash', hash('sha256', $token))->firstOrFail();
        abort_if($angket->expires_at->isPast(), 410, 'Tautan angket sudah kedaluwarsa.');

        return $angket;
    }

    private function authorizeRpl(Rpl $rpl, Request $request): void
    {
        $guruBkId = DB::table('guru_bk')->where('user_id', $request->user()->id)->value('id');
        abort_unless($guruBkId && (int) $rpl->guru_bk_id === (int) $guruBkId, 403);
    }
}
