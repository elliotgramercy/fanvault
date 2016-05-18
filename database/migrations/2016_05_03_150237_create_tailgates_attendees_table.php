<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTailgatesAttendeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('tailgates_attendees')){
            Schema::create('tailgates_attendees', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tailgate_id')->unsigned();
                $table->foreign('tailgate_id')->references('id')->on('tailgates');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users');
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
        if(Schema::hasTable('tailgates_attendees')){
            Schema::drop('tailgates_attendees');
        }
    }
}
