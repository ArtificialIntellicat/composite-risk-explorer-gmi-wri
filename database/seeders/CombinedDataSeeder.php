<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CombinedDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            AddGmiIndicatorsSeeder::class,
            WorldRiskIndexSeeder::class,
        ]);
    }
}
