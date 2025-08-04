<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CountryController;
use App\Models\Country;

Route::get('/countries', [CountryController::class, 'index']);

Route::get('/countries-with-score', [CountryController::class, 'countriesWithScore']);

Route::get('/countries/year/{year}', function ($year) {
    $countries = Country::with(['scores' => function ($query) use ($year) {
        $query->where('year', $year);
    }])->get();

    return $countries->map(function ($country) {
        $score = $country->scores->first();
        return [
            'name' => $country->name,
            'iso_code' => $country->iso_code,
            'latitude' => $country->latitude,
            'longitude' => $country->longitude,
            'region' => $country->region,
            'gmi_score' => $score?->gmi_score,
            'milex_indicator' => $score?->milex_indicator,
            'personnel_indicator' => $score?->personnel_indicator,
            'weapons_indicator' => $score?->weapons_indicator,
            'gmi_rank' => $score?->gmi_rank,
            'wri_score' => $score?->wri_score,
            'wri_exposure' => $score?->wri_exposure,
            'wri_vulnerability' => $score?->wri_vulnerability,
            'wri_susceptibility' => $score?->wri_susceptibility,
            'wri_coping_capacity' => $score?->wri_coping_capacity,
            'wri_adaptive_capacity' => $score?->wri_adaptive_capacity,
            'year' => $score?->year,
        ];
    });
});