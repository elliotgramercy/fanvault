<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('games_invites')){
            Schema::create('games_invites', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users');
                $table->integer('friend_id')->unsigned();
                $table->foreign('friend_id')->references('id')->on('users');
                $table->integer('game_id')->unsigned();
                $table->foreign('game_id')->references('id')->on('games');
                $table->enum('status',array('pending','accepted','declined'));
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
        if(Schema::hasTable('games_invites')){
            Schema::drop('games_invites');
        }
    }
}
