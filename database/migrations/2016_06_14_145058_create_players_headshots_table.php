<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayersHeadshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('players_headshots')){
            Schema::create('players_headshots', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('player_id')->unsigned();
                $table->foreign('player_id')->references('id')->on('players');
                $table->string('sr_image_id');
                $table->string('url');
                $table->string('size');
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
        if(Schema::hasTable('players_headshots')){
            Schema::drop('players_headshots');
        }
    }
}
