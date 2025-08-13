<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache; // <-- added
use App\Models\Country;

class MapDataController extends Controller
{
    /**
     * Return map data rows for a given year and a set of metrics.
     * - <= LAST_ACTUAL_YEAR  -> DB "actual" values
     * -  > LAST_ACTUAL_YEAR  -> file-based predictions (JSON), one row per country
     *
     * Frontend stays the same; the switch happens here.
     */
    public function forYear(Request $request): JsonResponse
    {
        $year    = (int) $request->query('year', 2022);
        $metrics = array_values(array_filter(explode(',', (string) $request->query('metrics', 'gmi_score'))));
        $LAST_ACTUAL_YEAR = 2022;

        // --- 1) Load actual values for that year from DB (used for <= 2022; also used for country names) ---
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

        if ($year <= $LAST_ACTUAL_YEAR) {
            return response()->json(array_values($actual));
        }

        // --- 2) Load predictions for years > LAST_ACTUAL_YEAR from file (prefer public/ to be Heroku-safe) ---
        $predPath = $this->resolvePredictionsPath();

        $predJson = null;
        if ($predPath) {
            // Build a cache key that changes when the file path or mtime changes
            $stamp    = @filemtime($predPath) ?: 0;
            $cacheKey = 'predictions_json_v1:' . md5($predPath . '|' . $stamp);

            // Cache for 10 minutes to avoid re-reading ~10–12 MB on every request
            $predJson = Cache::remember($cacheKey, 600, function () use ($predPath) {
                return $this->readJsonPossiblyGz($predPath);
            });
        }

        // Filter + reshape: one row per ISO with values for requested metrics
        $pred = [];
        if (is_array($predJson)) {
            foreach ($predJson as $p) {
                if ((int) Arr::get($p, 'year') !== $year) continue;

                $metric = Arr::get($p, 'metric');
                if (!in_array($metric, $metrics, true)) continue;

                $iso = strtoupper((string) Arr::get($p, 'iso3', ''));
                if (!$iso) continue;

                if (!isset($pred[$iso])) {
                    $pred[$iso] = [
                        'iso3'    => $iso,
                        'name'    => $actual[$iso]['name'] ?? null,
                        'year'    => $year,
                        'source'  => 'predicted',
                        'method'  => Arr::get($p, 'method'),
                        'version' => Arr::get($p, 'version'),
                    ];
                }

                $pred[$iso][$metric]               = Arr::get($p, 'value');
                $pred[$iso]["{$metric}_lo_ci"]     = Arr::get($p, 'lo_ci');
                $pred[$iso]["{$metric}_hi_ci"]     = Arr::get($p, 'hi_ci');
            }
        }

        // --- 3) Merge: prefer predicted rows; if missing, return skeleton with nulls so every ISO is present ---
        $out = [];
        $allIsos = array_unique(array_merge(array_keys($actual), array_keys($pred)));
        sort($allIsos);
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

    /** Prefer files baked into the slug under /public, which are stable on Heroku. */
    private function resolvePredictionsPath(): ?string
    {
        $candidates = [
            public_path('data/predictions.json'),
            public_path('data/predictions.json.gz'),
            public_path('predictions.json'),
            public_path('predictions.json.gz'),
            storage_path('app/predictions.json'),
            storage_path('app/predictions.json.gz'),
        ];
        foreach ($candidates as $p) {
            if (is_file($p)) return $p;
        }
        return null;
    }

    /** Read JSON from a path, supporting plain JSON or gzipped JSON (.gz). */
    private function readJsonPossiblyGz(?string $path): ?array
    {
        if (!$path || !is_file($path)) return null;
        try {
            $raw = file_get_contents($path);
            if ($raw === false) return null;

            if (Str::endsWith($path, '.gz')) {
                $raw = gzdecode($raw);
                if ($raw === false) return null;
            }
            $data = json_decode($raw, true, flags: JSON_INVALID_UTF8_SUBSTITUTE);
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            return null; // fail-soft
        }
    }
}
