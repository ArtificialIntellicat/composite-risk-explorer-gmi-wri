<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Country;

class MapDataController extends Controller
{
    public function forYear(Request $request)
    {
        $year = (int) $request->query('year', 2022);
        $metrics = array_filter(explode(',', $request->query('metrics', 'gmi_score')));
        $LAST_ACTUAL_YEAR = 2022;

        // 1) Echte Werte für das Jahr ziehen
        $countries = Country::with(['scores' => function ($q) use ($year) {
            $q->where('year', $year);
        }])->get();

        $actual = [];
        foreach ($countries as $c) {
            $iso = strtoupper((string) $c->iso_code);
            $row = [
                'iso3'   => $iso,
                'name'   => $c->name,
                'year'   => $year,
                'source' => 'actual',
            ];
            $score = $c->scores->first();
            foreach ($metrics as $m) {
                $row[$m] = $score?->$m;
            }
            $actual[$iso] = $row;
        }

        // 2) Prognosen laden (nur für Jahre > 2022)
        $pred = [];
        if ($year > $LAST_ACTUAL_YEAR && Storage::exists('predictions.json')) {
            $json = json_decode(Storage::get('predictions.json'), true) ?? [];
            foreach ($json as $p) {
                if ((int)($p['year'] ?? 0) !== $year) continue;
                if (!in_array($p['metric'] ?? '', $metrics, true)) continue;
                $iso = strtoupper((string)($p['iso3'] ?? ''));
                if (!$iso) continue;

                $pred[$iso] ??= [
                    'iso3'    => $iso,
                    'name'    => $actual[$iso]['name'] ?? null,
                    'year'    => $year,
                    'source'  => 'predicted',
                    'method'  => $p['method'] ?? null,
                    'version' => $p['version'] ?? null,
                ];
                $pred[$iso][$p['metric']] = $p['value'] ?? null;
                $pred[$iso][$p['metric'].'_lo_ci'] = $p['lo_ci'] ?? null;
                $pred[$iso][$p['metric'].'_hi_ci'] = $p['hi_ci'] ?? null;
            }
        }

        // 3) Mischung: ≤2022 → actual; ≥2023 → predicted (falls fehlt: Nulls)
        if ($year <= $LAST_ACTUAL_YEAR) {
            return response()->json(array_values($actual));
        }

        $out = [];
        $allIsos = array_unique(array_merge(array_keys($actual), array_keys($pred)));
        foreach ($allIsos as $iso) {
            if (isset($pred[$iso])) {
                $out[] = $pred[$iso];
            } else {
                $row = $actual[$iso] ?? ['iso3' => $iso, 'name' => null, 'year' => $year];
                $row['source'] = 'predicted';
                foreach ($metrics as $m) {
                    $row[$m] = $row[$m] ?? null;
                }
                $out[] = $row;
            }
        }

        return response()->json($out);
    }
}
