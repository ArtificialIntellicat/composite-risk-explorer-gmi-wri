<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CountryScoreSeeder extends Seeder
{
    public function run()
    {
        $file = storage_path('app/data/GMI-2023-all-years.xlsx');
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheetByName('GMI Data');

        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $iso3      = $sheet->getCell('A' . $row)->getValue();
            $year      = (int) $sheet->getCell('D' . $row)->getValue();
            $scoreRaw  = $sheet->getCell('H' . $row)->getValue();
            $score     = is_numeric($scoreRaw) ? (float) $scoreRaw : null;

            // Country anhand des ISO-Codes finden
            $country = Country::where('iso_code', $iso3)->first();

            if ($country && $score !== null) {
                DB::table('country_scores')->updateOrInsert(
                    [
                        'country_id' => $country->id,
                        'year' => $year,
                    ],
                    [
                        'gmi_score' => $score,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        echo "GMI Score Daten wurden erfolgreich importiert.\n";
    }
}
