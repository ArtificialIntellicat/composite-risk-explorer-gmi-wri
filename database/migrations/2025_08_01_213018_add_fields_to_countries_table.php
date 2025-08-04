<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_code', 3)->nullable()->after('id');
            $table->string('region')->nullable()->after('name');
            $table->float('gmi_score')->nullable()->after('region');
            $table->integer('gmi_rank')->nullable()->after('gmi_score');
        });
    }

    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['iso_code', 'region', 'gmi_score', 'gmi_rank']);
        });
    }

};
