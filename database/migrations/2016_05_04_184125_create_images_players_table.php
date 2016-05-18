<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('images_players')){
            Schema::create('images_players', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('image_id')->unsigned();
                $table->foreign('image_id')->references('id')->on('images');
                $table->string('player');
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
        if(Schema::hasTable('images_players')){
            Schema::drop('images_players');
        }
    }
}
