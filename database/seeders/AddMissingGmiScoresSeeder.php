<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\CountryScore;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AddMissingGmiScoresSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/data/GMI-2023-all-years.xlsx');
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('GMI Data');
        $rows = $sheet->toArray(null, true, true, true);

        $header = array_shift($rows); // Skip header row
        $insertedCountries = 0;
        $insertedScores = 0;
        $skippedScores = 0;

        foreach ($rows as $row) {
            $iso = strtoupper(trim($row['A']));
            $name = trim($row['B']);
            $region = trim($row['C']);
            $year = (int) $row['D'];
            $score = is_numeric($row['E']) ? floatval($row['E']) : null;
            $rank = is_numeric($row['F']) ? intval($row['F']) : null;

            if (!$iso || !$year || $score === null) {
                continue; // Skip incomplete rows
            }

            // 1. Insert missing countries
            $country = Country::firstOrCreate(
                ['iso_code' => $iso],
                ['name' => $name, 'region' => $region, 'gmi_score' => null, 'gmi_rank' => null]
            );
            if ($country->wasRecentlyCreated) {
                $insertedCountries++;
                $this->command->info("➕ Country created: {$name} ({$iso})");
            }

            // 2. Insert missing GMI year score
            $existing = CountryScore::where('country_id', $country->id)
                ->where('year', $year)
                ->exists();

            if (!$existing) {
                CountryScore::create([
                    'country_id' => $country->id,
                    'year' => $year,
                    'gmi_score' => $score,
                ]);
                $insertedScores++;
            } else {
                $skippedScores++;
            }

            // 3. Update latest country.gmi_score / gmi_rank if it's the most recent year (2022)
            if ($year === 2022) {
                $country->update([
                    'gmi_score' => $score,
                    'gmi_rank' => $rank,
                ]);
            }
        }

        $this->command->info("Seeder finished:");
        $this->command->info("   - Countries added: {$insertedCountries}");
        $this->command->info("   - GMI scores added: {$insertedScores}");
        $this->command->info("   - GMI scores skipped (already present): {$skippedScores}");
    }
}
