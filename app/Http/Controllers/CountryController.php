<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{
    public function index()
    {
        Log::info('CountryController@index wurde aufgerufen');

        $countries = Country::all();
        Log::info('Anzahl Länder: ' . $countries->count());

        return response()->json($countries);
    }

    public function countriesWithScore(Request $request)
    {
        $year = $request->query('year', now()->year);

        $countries = Country::select(
            'countries.*',
            'cs.gmi_score',
            'cs.year',
            'countries.latitude',
            'countries.longitude'
        )
        ->join('country_scores as cs', 'cs.country_id', '=', 'countries.id')
        ->where('cs.year', $year)
        ->get();

        return response()->json($countries);
    }
}
