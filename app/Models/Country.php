<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso_code', 3)->unique();
            $table->string('region')->nullable();
            $table->timestamps();
        });
    }

    protected $fillable = [
        'name', 'iso_code', 'region'
    ];

    public function scores()
    {
        return $this->hasMany(CountryScore::class);
    }

}
