<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('games_scores')){
            Schema::create('games_scores', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('game_id')->unsigned();
                $table->foreign('game_id')->references('id')->on('games');
                $table->integer('team_id')->unsigned();
                $table->foreign('team_id')->references('id')->on('teams');
                $table->integer('hits')->default(0);
                $table->integer('runs')->default(0);
                $table->integer('errors')->default(0);
                $table->text('inning_scores')->default('');
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
        if(Schema::hasTable('games_scores')){
            Schema::drop('games_scores');
        }
    }
}
