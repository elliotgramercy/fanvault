<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFvMarketAbbrToTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('teams')){
            Schema::table('teams', function ($table) {
                $table->string('fv_market_abbr');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('teams')){
            Schema::table('teams', function ($table) {
                $table->string('fv_market_abbr');
            });
        }
    }
}
