<?php

namespace App\Services;

class ClimateRisk
{
    public string $country;
    public float $gmi;
    public float $climate;

    public function __construct(string $country, float $gmi, float $climate)
    {
        $this->country = $country;
        $this->gmi = $gmi;
        $this->climate = $climate;
    }

    public function riskProfile(): string
    {
        $score = ($this->gmi + $this->climate) / 2;
        if ($score > 75) {
            return 'hoch';
        } elseif ($score > 50) {
            return 'mittel';
        }
        return 'niedrig';
    }
}
