<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesOfficialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('games_officials')){
            Schema::create('games_officials', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('official_id')->unsigned();
                $table->foreign('official_id')->references('id')->on('officials');
                $table->integer('game_id')->unsigned();
                $table->foreign('game_id')->references('id')->on('games');
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
        if(Schema::hasTable('games_officials')){
            Schema::drop('games_officials');
        }
    }
}
