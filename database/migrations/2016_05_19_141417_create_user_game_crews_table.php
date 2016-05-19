<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGameCrewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('user_game_crews')){
            Schema::create('user_game_crews', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users');
                $table->integer('game_id')->unsigned();
                $table->foreign('game_id')->references('id')->on('games');
                $table->integer('crew_member_user_id')->unsigned()->nullable();
                $table->foreign('crew_member_user_id')->references('id')->on('users');
                $table->string('crew_member_first_name')->nullable();
                $table->string('crew_member_last_name')->nullable();
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
        if(Schema::hasTable('user_game_crews')){
            Schema::drop('user_game_crews');
        }
    }
}
