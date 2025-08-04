<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCountryTablesForGmi extends Migration
{
    public function up()
    {
        // Entferne Felder aus countries
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('gmi_score');
            $table->dropColumn('gmi_rank');
        });

        // Füge neue Felder zu country_scores hinzu
        Schema::table('country_scores', function (Blueprint $table) {
            $table->float('milex_indicator')->nullable()->after('year');
            $table->float('personnel_indicator')->nullable()->after('milex_indicator');
            $table->float('weapons_indicator')->nullable()->after('personnel_indicator');
            $table->integer('gmi_rank')->nullable()->after('gmi_score');
        });
    }

    public function down()
    {
        // Rolle rückwärts, falls nötig
        Schema::table('countries', function (Blueprint $table) {
            $table->float('gmi_score')->nullable();
            $table->integer('gmi_rank')->nullable();
        });

        Schema::table('country_scores', function (Blueprint $table) {
            $table->dropColumn('milex_indicator');
            $table->dropColumn('personnel_indicator');
            $table->dropColumn('weapons_indicator');
            $table->dropColumn('gmi_rank');
        });
    }
}
