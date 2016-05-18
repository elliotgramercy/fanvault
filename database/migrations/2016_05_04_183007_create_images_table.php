<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('images')){
            Schema::create('images', function (Blueprint $table) {
                $table->increments('id');
                $table->string('sr_image_id');
                $table->integer('venue_id')->unsigned();
                $table->foreign('venue_id')->references('id')->on('venues');
                $table->dateTime('date_created');
                $table->string('url');
                $table->enum('type',array('action','venue'));
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
        if(Schema::hasTable('images')){
            Schema::drop('images');
        }
    }
}
