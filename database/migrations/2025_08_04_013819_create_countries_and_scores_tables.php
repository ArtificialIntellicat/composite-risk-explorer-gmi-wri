<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso_code', 3)->unique();
            $table->string('region')->nullable();
            $table->timestamps();
        });

        Schema::create('country_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->float('gmi_score')->nullable();
            $table->integer('gmi_rank')->nullable();
            $table->float('milex_indicator')->nullable();
            $table->float('personnel_indicator')->nullable();
            $table->float('weapons_indicator')->nullable();
            $table->float('wri_score')->nullable();
            $table->float('wri_exposure')->nullable();
            $table->float('wri_vulnerability')->nullable();
            $table->float('wri_susceptibility')->nullable();
            $table->float('wri_coping_capacity')->nullable();
            $table->float('wri_adaptive_capacity')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_scores');
        Schema::dropIfExists('countries');
    }
};
