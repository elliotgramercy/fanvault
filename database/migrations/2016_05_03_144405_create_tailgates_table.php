<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTailgatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('tailgates')){
            Schema::create('tailgates', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('creator_id')->unsigned();
                $table->foreign('creator_id')->references('id')->on('users');
                $table->string('title');
                $table->text('description');
                $table->decimal('cost', 6, 2);
                $table->integer('game_id')->unsigned();
                $table->foreign('game_id')->references('id')->on('games');
                $table->decimal('lng', 10, 7);
                $table->decimal('lat', 10, 7);
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
        if(Schema::hasTable('tailgates')){
            Schema::drop('tailgates');
        }
    }
}
