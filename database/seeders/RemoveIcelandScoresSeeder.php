<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RemoveIcelandScoresSeeder extends Seeder
{
    public function run()
    {
        $countryId = DB::table('countries')->where('iso_code', 'ISL')->value('id');

        if ($countryId) {
            DB::table('country_scores')->where('country_id', $countryId)->delete();
            $this->command->info("Alle GMI-Daten für Island (ISL) wurden entfernt.");
        } else {
            $this->command->warn("Island (ISL) nicht gefunden – keine Daten entfernt.");
        }
    }
}
