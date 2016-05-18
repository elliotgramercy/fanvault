<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFinalScoresToGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('games')){
            Schema::table('games', function ($table) {
                $table->integer('home_team_runs');
                $table->integer('away_team_runs');
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
        if(Schema::hasTable('games')){
            Schema::table('games', function ($table) {
                $table->dropColumn('home_team_runs');
                $table->dropColumn('away_team_runs');
            });
        }
    }
}
