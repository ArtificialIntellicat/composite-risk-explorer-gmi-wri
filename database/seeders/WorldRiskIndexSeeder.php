<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\CountryScore;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class WorldRiskIndexSeeder extends Seeder
{
    public function run()
    {
        $basePath = storage_path('app/data/worldriskindex');
        $added = 0;
        $notFoundCountries = [];
        $noScores = [];

        foreach (range(2000, 2024) as $year) {
            $filename = "{$basePath}/worldriskindex-{$year}.csv";

            if (!file_exists($filename)) {
                $this->command->warn("Datei nicht gefunden: worldriskindex-{$year}.csv");
                continue;
            }

            $csv = Reader::createFromPath($filename, 'r');
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();

            foreach ($records as $record) {
                $iso = strtoupper(trim($record['ISO3'] ?? ''));
                if (!$iso) continue;

                $country = Country::where('iso_code', $iso)->first();

                if (!$country) {
                    $this->command->warn("Country not found: {$record['Country']} ({$iso})");
                    $notFoundCountries[] = $record['Country'];
                    continue;
                }

                $score = CountryScore::where('country_id', $country->id)->where('year', $year)->first();

                if (!$score) {
                    $this->command->warn("CountryScore not found: {$record['Country']} ({$iso})");
                    continue;
                }

                if ($score) {
                    $score->wri_score = self::floatOrNull($record['W']);
                    $score->wri_exposure = self::floatOrNull($record['E']);
                    $score->wri_vulnerability = self::floatOrNull($record['V']);
                    $score->wri_susceptibility = self::floatOrNull($record['S']);
                    $score->wri_coping_capacity = self::floatOrNull($record['C']);
                    $score->wri_adaptive_capacity = self::floatOrNull($record['A']);
                    $score->save();

                    $this->command->info("current values: score {$score->wri_score}, exposure {$score->wri_exposure}, vulnerability {$score->wri_vulnerability}, susceptibility {$score->wri_susceptibility}, coping capacity {$score->wri_coping_capacity}, adaptive capacity {$score->wri_adaptive_capacity}, for {$record['Country']} ({$iso}) in {$year}.");

                    $this->command->info("{$record['Country']} ({$iso}, {$year}): updated WRI values.");
                    $added++;
                } else {
                    $this->command->warn("No score entry for {$record['Country']} ({$iso}) in {$year}.");
                    $noScores[] = "{$record['Country']} ({$year})";
                }
            }
        }

        $this->command->info("Scores updated: {$added}");
        if (!empty($notFoundCountries)) {
            $this->command->warn("Countries not found: " . implode(', ', array_unique($notFoundCountries)));
        }
        if (!empty($noScores)) {
            $this->command->warn("No country_scores found for: " . implode(', ', array_unique($noScores)));
        }
    }

    private static function floatOrNull($value)
    {
        $value = str_replace(',', '.', $value); // handle decimal comma
        return is_numeric($value) ? (float)$value : null;
    }
}