<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CountrySeeder extends Seeder
{
    public function run()
    {
        $file = storage_path('app/data/GMI-2023-all-years.xlsx');
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheetByName('GMI Data');

        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $year = (int) $sheet->getCell('D'.$row)->getValue();
            if ($year !== 2022) {
                continue; // nur aktuellster Jahrgang
            }
            $iso3   = $sheet->getCell('A'.$row)->getValue();
            $name   = $sheet->getCell('B'.$row)->getValue();
            $region = $sheet->getCell('C'.$row)->getValue();

            Country::updateOrCreate(
                ['iso_code' => $iso3],
                ['name' => $name, 'region' => $region]
            );
        }
    }
}
