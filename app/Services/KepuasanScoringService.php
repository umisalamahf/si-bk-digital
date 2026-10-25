<?php

namespace App\Services;

use Illuminate\Support\Collection;

class KepuasanScoringService
{
    public const LABELS = [1 => 'STS', 2 => 'TS', 3 => 'S', 4 => 'SS'];

    public const QUESTIONS = [
        'kejelasan' => 'Tujuan dan kegiatan layanan disampaikan dengan jelas.',
        'kesesuaian' => 'Materi dan layanan sesuai dengan kebutuhan saya.',
        'kenyamanan' => 'Saya merasa nyaman dan dihargai selama layanan.',
        'manfaat' => 'Layanan ini bermanfaat bagi saya.',
        'tindak_lanjut' => 'Tindak lanjut atau langkah berikutnya disampaikan dengan jelas.',
    ];

    public function summarize(Collection $responses): array
    {
        $summary = [];

        foreach (self::QUESTIONS as $key => $question) {
            $scores = $responses
                ->map(fn ($response) => (int) ($response->jawaban[$key] ?? 0))
                ->filter(fn ($score) => $score >= 1 && $score <= 4);
            $average = $scores->isEmpty() ? null : round($scores->avg(), 2);

            $summary[$key] = [
                'question' => $question,
                'count' => $scores->count(),
                'average' => $average,
                'percentage' => $average === null ? null : round(($average / 4) * 100, 1),
            ];
        }

        return $summary;
    }
}
