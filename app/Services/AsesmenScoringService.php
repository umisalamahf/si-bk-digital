<?php

namespace App\Services;

use App\Models\AsesmenInstrumen;
use Illuminate\Validation\ValidationException;

class AsesmenScoringService
{
    public function score(AsesmenInstrumen $instrument, array $answers): array
    {
        $configuration = $instrument->konfigurasi_skor;
        $isProfile = is_array($configuration) && ($configuration['scoring_mode'] ?? null) === 'profile';
        if (!is_array($configuration) || empty($configuration['aspects']) ||
            ($isProfile ? empty($configuration['aspect_levels']) : empty($configuration['ranges']))) {
            throw ValidationException::withMessages([
                'jawaban' => 'Konfigurasi skoring instrumen belum tersedia. Hubungi Guru BK.',
            ]);
        }

        $reverseItems = array_map('intval', $configuration['reverse_items'] ?? []);
        $aspectForItem = [];
        foreach ($configuration['aspects'] as $aspect) {
            $numbers = $aspect['items'] ?? range((int) $aspect['start'], (int) $aspect['end']);
            foreach ($numbers as $number) {
                $aspectForItem[$number] = $aspect['label'];
            }
        }

        $aspectScores = [];
        $scoreTotal = 0;
        foreach ($instrument->butir as $item) {
            $number = (int) $item->nomor;
            if (!array_key_exists((string) $item->id, $answers) || !isset($aspectForItem[$number])) {
                throw ValidationException::withMessages([
                    'jawaban' => 'Semua butir instrumen wajib dijawab dengan data yang valid.',
                ]);
            }

            $response = (int) $answers[(string) $item->id];
            if ($response < 1 || $response > 4) {
                throw ValidationException::withMessages([
                    'jawaban' => 'Nilai jawaban instrumen tidak valid.',
                ]);
            }

            $scored = in_array($number, $reverseItems, true) ? $response : 5 - $response;
            $label = $aspectForItem[$number];
            $aspectScores[$label] = ($aspectScores[$label] ?? 0) + $scored;
            $scoreTotal += $scored;
        }

        if ($isProfile) {
            $topScore = max($aspectScores);
            $highlightedAspects = array_keys(array_filter(
                $aspectScores,
                fn (int $score) => $score === $topScore
            ));
            $aspectInterpretations = [];
            foreach ($aspectScores as $label => $score) {
                $level = collect($configuration['aspect_levels'])->first(
                    fn (array $item) => $score >= (int) $item['min'] && $score <= (int) $item['max']
                );
                if (!$level) {
                    throw ValidationException::withMessages([
                        'jawaban' => "Skor aspek {$label} berada di luar rentang interpretasi yang ditetapkan.",
                    ]);
                }
                $aspectInterpretations[$label] = $level['label'];
            }

            $category = $configuration['result_label'] ?? 'Profil refleksi diri';
            $conclusion = 'Area yang paling terasa sesuai saat ini: ' . implode(', ', $highlightedAspects) . '.';
            $recommendation = $configuration['reflection_prompt']
                ?? 'Gunakan hasil ini sebagai bahan refleksi dan diskusikan dengan Guru BK.';
            $lowestAspects = [];
        } else {
            $range = collect($configuration['ranges'])->first(
                fn (array $item) => $scoreTotal >= (int) $item['min'] && $scoreTotal <= (int) $item['max']
            );
            if (!$range) {
                throw ValidationException::withMessages([
                    'jawaban' => 'Skor berada di luar rentang interpretasi yang ditetapkan.',
                ]);
            }

            asort($aspectScores);
            $lowestAspects = [];
            if ($aspectScores !== []) {
                $lowestScore = reset($aspectScores);
                $lowestAspects = array_keys(array_filter(
                    $aspectScores,
                    fn (int $score) => $score === $lowestScore
                ));
            }

            $followUp = $configuration['follow_up'] ?? [];
            $recommendations = array_map(
                fn (string $aspect) => $followUp[$aspect] ?? "Tinjau kebutuhan layanan pada aspek {$aspect}.",
                $lowestAspects
            );
            $category = $range['label'];
            $conclusion = "{$range['label']} (skor {$scoreTotal}).";
            $recommendation = $recommendations === []
                ? 'Tinjau hasil instrumen bersama Guru BK.'
                : implode(' ', $recommendations);
        }

        return [
            'score_total' => $scoreTotal,
            'category' => $category,
            'aspects' => $aspectScores,
            'lowest_aspects' => $lowestAspects,
            'highlighted_aspects' => $isProfile ? $highlightedAspects : [],
            'aspect_interpretations' => $isProfile ? $aspectInterpretations : [],
            'mode' => $isProfile ? 'profile' : 'scored',
            'disclaimer' => $isProfile ? ($configuration['disclaimer'] ?? null) : null,
            'conclusion' => $conclusion,
            'recommendation' => $recommendation,
        ];
    }
}
