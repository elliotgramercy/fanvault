<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTailgatesTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('tailgates_tags')){
            Schema::create('tailgates_tags', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tailgate_id')->unsigned();
                $table->foreign('tailgate_id')->references('id')->on('tailgates');
                $table->integer('tag_id')->unsigned();
                $table->foreign('tag_id')->references('id')->on('tags');
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
        if(Schema::hasTable('tailgates_tags')){
            Schema::drop('tailgates_tags');
        }
    }
}
