<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendsInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('friends_invites')){
            Schema::create('friends_invites', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user')->unsigned();
                $table->foreign('user')->references('id')->on('users');
                $table->bigInteger('invited_fb_id')->unsigned();
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
        if(Schema::hasTable('friends_invites')){
            Schema::drop('friends_invites');
        }
    }
}
