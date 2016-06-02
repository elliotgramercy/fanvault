<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGameIdAndTeamIdToGamesPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('games_players')){
            Schema::table('games_players', function ($table) {
                $table->integer('game_id')->unsigned();
                $table->foreign('game_id')->references('id')->on('games');
                $table->integer('team_id')->unsigned();
                $table->foreign('team_id')->references('id')->on('teams');
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
        if(Schema::hasTable('games_players')){
            Schema::table('games_players', function ($table) {
                $table->dropColumn('game_id');
                $table->dropColumn('team_id');
            });
        }
    }
}
