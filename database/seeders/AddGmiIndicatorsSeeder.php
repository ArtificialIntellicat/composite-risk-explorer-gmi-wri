<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\CountryScore;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AddGmiIndicatorsSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/data/GMI-2023-all-years.xlsx');
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('GMI Data');
        $rows = $sheet->toArray(null, true, true, true);

        $header = array_shift($rows); // Skip header row
        $addedScores = 0;
        $updatedScores = 0;
        $missingCountries = [];

        foreach ($rows as $row) {
            $iso = strtoupper(trim($row['A'])); // iso3_code
            $name = trim($row['B']);            // country
            $region = trim($row['C']);          // region
            $year = (int) $row['D'];            // year

            // Indicators
            $milex = is_numeric($row['E']) ? floatval($row['E']) : null;
            $personnel = is_numeric($row['F']) ? floatval($row['F']) : null;
            $weapons = is_numeric($row['G']) ? floatval($row['G']) : null;
            $score = is_numeric($row['H']) ? floatval($row['H']) : null;
            $rank = is_numeric($row['I']) ? intval($row['I']) : null;

            if (!$iso || !$year) continue;

            $country = Country::where('iso_code', $iso)->first();
            if (!$country) {
                $missingCountries[] = $iso;
                continue;
            }

            $scoreEntry = CountryScore::updateOrCreate(
                ['country_id' => $country->id, 'year' => $year],
                [
                    'milex_indicator' => $milex,
                    'personnel_indicator' => $personnel,
                    'weapons_indicator' => $weapons,
                    'gmi_score' => $score,
                    'gmi_rank' => $rank
                ]
            );

            if ($scoreEntry->wasRecentlyCreated) {
                $addedScores++;
            } else {
                $updatedScores++;
            }
        }

        $this->command->info("Seeder completed.");
        $this->command->info("   - Scores added: $addedScores");
        $this->command->info("   - Scores updated: $updatedScores");
        if (!empty($missingCountries)) {
            $unique = implode(', ', array_unique($missingCountries));
            $this->command->warn("   - Countries not found in DB (ISO): $unique");
        }
    }
}
