<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Country;

class ExportMetrics extends Command
{
    protected $signature = 'data:export-metrics {--out= : Output CSV path relative to project root}';
    protected $description = 'Export country-year metrics to a single CSV for ML (GMI & WRI)';

    public function handle()
    {
        $outRel = $this->option('out') ?: 'ml/data_proc/combined.csv';
        $outPath = base_path($outRel);
        $dir = dirname($outPath);

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $fh = fopen($outPath, 'w');
        $headers = [
            'iso3','name','region','year',
            'gmi_score','milex_indicator','personnel_indicator','weapons_indicator','gmi_rank',
            'wri_score','wri_exposure','wri_vulnerability','wri_susceptibility','wri_coping_capacity','wri_adaptive_capacity',
        ];
        fputcsv($fh, $headers);

        $countries = Country::with(['scores' => fn($q) => $q->orderBy('year')])->get();
        $rows = 0;

        foreach ($countries as $c) {
            $iso = strtoupper($c->iso_code);
            foreach ($c->scores as $s) {
                fputcsv($fh, [
                    $iso,
                    $c->name,
                    $c->region,
                    $s->year,
                    $s->gmi_score,
                    $s->milex_indicator,
                    $s->personnel_indicator,
                    $s->weapons_indicator,
                    $s->gmi_rank,
                    $s->wri_score,
                    $s->wri_exposure,
                    $s->wri_vulnerability,
                    $s->wri_susceptibility,
                    $s->wri_coping_capacity,
                    $s->wri_adaptive_capacity,
                ]);
                $rows++;
            }
        }
        fclose($fh);

        $this->info("Wrote {$rows} rows → {$outRel}");
        return self::SUCCESS;
    }
}
