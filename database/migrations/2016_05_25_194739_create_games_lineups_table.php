<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesLineupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('games_lineups')){
            Schema::create('games_lineups', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('game_id')->unsigned();
                $table->foreign('game_id')->references('id')->on('games');
                $table->integer('player_id')->unsigned();
                $table->foreign('player_id')->references('id')->on('players');
                $table->integer('team_id')->unsigned();
                $table->foreign('team_id')->references('id')->on('teams');
                $table->integer('position_num');
                $table->string('position');
                $table->timestamps();
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
        if(Schema::hasTable('games_lineups')){
            Schema::drop('games_lineups');
        }
    }
}
