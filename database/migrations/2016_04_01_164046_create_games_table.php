<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if(!Schema::hasTable('games')){
            Schema::create('games', function (Blueprint $table) {
                $table->increments('id');
                $table->string('sr_league_id');
                $table->string('league_name');
                $table->string('league_alias');
                $table->string('sr_season_id');
                $table->string('season_year');
                $table->string('season_type');
                $table->string('sr_game_id');
                $table->string('status');
                $table->string('coverage');
                $table->integer('game_number');
                $table->enum('day_night',array('D','N'));
                $table->dateTime('scheduled');
                $table->integer('home_team_id')->unsigned();
                $table->foreign('home_team_id')->references('id')->on('teams');
                $table->integer('away_team_id')->unsigned();
                $table->foreign('away_team_id')->references('id')->on('teams');
                $table->integer('venue_id')->unsigned();
                $table->foreign('venue_id')->references('id')->on('venues');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if(Schema::hasTable('games')){
            Schema::drop('games');
        }
    }
}
