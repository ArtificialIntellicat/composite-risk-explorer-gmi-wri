<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryScore extends Model
{
    protected $fillable = [
        'country_id',
        'year',
        'gmi_score',
        'milex_indicator',
        'personnel_indicator',
        'weapons_indicator',
        'wri_score',
        'wri_exposure',
        'wri_vulnerability',
        'wri_susceptibility',
        'wri_coping_capacity',
        'wri_adaptive_capacity',
    ];


    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
