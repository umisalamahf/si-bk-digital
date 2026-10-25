<?php

namespace Tests\Unit;

use App\Models\AsesmenButir;
use App\Models\AsesmenInstrumen;
use App\Services\AsesmenScoringService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AsesmenScoringServiceTest extends TestCase
{
    public function test_scores_positive_and_reverse_keyed_responses_and_returns_lowest_aspects(): void
    {
        $instrument = $this->instrument([
            'reverse_items' => [2],
            'ranges' => [
                ['min' => 6, 'max' => 6, 'label' => 'Tinggi'],
                ['min' => 1, 'max' => 5, 'label' => 'Rendah'],
            ],
            'aspects' => [
                ['label' => 'Konsentrasi', 'items' => [1]],
                ['label' => 'Kemandirian', 'items' => [2]],
            ],
            'follow_up' => ['Kemandirian' => 'Tinjau kemandirian.'],
        ], [1, 2]);

        $result = (new AsesmenScoringService())->score($instrument, [
            '101' => 1,
            '102' => 2,
        ]);

        $this->assertSame(6, $result['score_total']);
        $this->assertSame('Tinggi', $result['category']);
        $this->assertSame(['Kemandirian'], $result['lowest_aspects']);
        $this->assertSame('Tinjau kemandirian.', $result['recommendation']);
    }

    public function test_rejects_answers_outside_the_documented_scale(): void
    {
        $instrument = $this->instrument([
            'ranges' => [['min' => 4, 'max' => 4, 'label' => 'Cukup']],
            'aspects' => [['label' => 'Aspek', 'items' => [1]]],
        ], [1]);

        $this->expectException(ValidationException::class);
        (new AsesmenScoringService())->score($instrument, ['101' => 5]);
    }

    public function test_profile_scoring_highlights_tied_areas_without_a_diagnostic_category(): void
    {
        $instrument = $this->instrument([
            'scoring_mode' => 'profile',
            'result_label' => 'Profil eksplorasi',
            'aspect_levels' => [
                ['min' => 4, 'max' => 8, 'label' => 'Belum banyak terasa'],
                ['min' => 9, 'max' => 12, 'label' => 'Mulai terasa'],
                ['min' => 13, 'max' => 16, 'label' => 'Sering terasa'],
            ],
            'aspects' => [
                ['label' => 'Kreatif', 'items' => [1, 2, 3, 4]],
                ['label' => 'Sosial', 'items' => [5, 6, 7, 8]],
            ],
            'disclaimer' => 'Bahan refleksi, bukan diagnosis.',
        ], range(1, 8));

        $answers = array_fill_keys(array_map(fn (int $number) => (string) (100 + $number), range(1, 8)), 1);
        $result = (new AsesmenScoringService())->score($instrument, $answers);

        $this->assertSame('profile', $result['mode']);
        $this->assertSame('Profil eksplorasi', $result['category']);
        $this->assertSame(['Kreatif', 'Sosial'], $result['highlighted_aspects']);
        $this->assertSame('Sering terasa', $result['aspect_interpretations']['Kreatif']);
        $this->assertSame('Bahan refleksi, bukan diagnosis.', $result['disclaimer']);
    }

    private function instrument(array $configuration, array $numbers): AsesmenInstrumen
    {
        $instrument = new AsesmenInstrumen();
        $instrument->konfigurasi_skor = $configuration;
        $items = collect($numbers)->map(function (int $number) {
            $item = new AsesmenButir(['nomor' => $number]);
            $item->id = 100 + $number;

            return $item;
        });
        $instrument->setRelation('butir', $items);

        return $instrument;
    }
}
