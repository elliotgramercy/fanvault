<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('friends')){
            Schema::create('friends', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_1')->unsigned();
                $table->foreign('user_1')->references('id')->on('users');
                $table->integer('user_2')->unsigned();
                $table->foreign('user_2')->references('id')->on('users');
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
        if(Schema::hasTable('friends')){
            Schema::drop('friends');
        }
    }
}
