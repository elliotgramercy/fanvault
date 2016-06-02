<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('games_players')){
            Schema::create('games_players', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('player_id')->unsigned();
                $table->foreign('player_id')->references('id')->on('players');
                $table->boolean('is_pitcher')->default(false);
                $table->integer('pitching_er')->default(0);
                $table->decimal('pitching_era',5,3)->default(0);
                $table->integer('pitching_so')->default(0);
                $table->integer('pitching_bb')->default(0);
                $table->integer('pitching_h')->default(0);
                $table->integer('pitching_r')->default(0);
                $table->integer('pitching_ip')->default(0);
                $table->integer('pitching_bf')->default(0);
                $table->decimal('pitching_gofo',5,3)->default(0);
                $table->integer('hitting_h')->default(0);
                $table->integer('hitting_bb')->default(0);
                $table->integer('hitting_so')->default(0);
                $table->decimal('hitting_avg',5,3)->default(0);
                $table->integer('hitting_rbi')->default(0);
                $table->integer('hitting_r')->default(0);
                $table->integer('hitting_ab')->default(0);
                $table->integer('hitting_d')->default(0);
                $table->integer('hitting_hr')->default(0);
                $table->integer('hitting_stolen')->default(0);
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
        if(Schema::hasTable('games_players')){
            Schema::drop('games_players');
        }
    }
}
