<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class AddMissingCountriesSeeder extends Seeder
{
    public function run()
    {
        $countries = [
            ['iso_code' => 'ISL', 'name' => 'Iceland',         'region' => 'Europe'],
            ['iso_code' => 'GRL', 'name' => 'Greenland',       'region' => 'Americas'],
            ['iso_code' => 'LBY', 'name' => 'Libya',           'region' => 'Africa'],
            ['iso_code' => 'SOM', 'name' => 'Somalia',         'region' => 'Africa'],
            ['iso_code' => 'ERI', 'name' => 'Eritrea',         'region' => 'Africa'],
            ['iso_code' => 'UZB', 'name' => 'Uzbekistan',      'region' => 'Asia'],
            ['iso_code' => 'TKM', 'name' => 'Turkmenistan',    'region' => 'Asia'],
            ['iso_code' => 'VNM', 'name' => 'Vietnam',         'region' => 'Asia'],
            ['iso_code' => 'LAO', 'name' => 'Laos',            'region' => 'Asia'],
            ['iso_code' => 'SUR', 'name' => 'Suriname',        'region' => 'Americas'],
            ['iso_code' => 'YEM', 'name' => 'Yemen',           'region' => 'Asia'],
            ['iso_code' => 'ARE', 'name' => 'United Arab Emirates', 'region' => 'Asia'],
        ];

        foreach ($countries as $c) {
            Country::updateOrCreate(
                ['iso_code' => $c['iso_code']],
                [
                    'name' => $c['name'],
                    'region' => $c['region']
                ]
            );
            $this->command->info("Inserted/updated country: {$c['name']} ({$c['iso_code']})");
        }
    }
}
