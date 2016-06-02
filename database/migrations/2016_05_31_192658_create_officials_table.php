<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('officials')){
            Schema::create('officials', function (Blueprint $table) {
                $table->increments('id');
                $table->string('sr_official_id');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('assignment');
                $table->string('experience');
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
        if(Schema::hasTable('officials')){
            Schema::drop('officials');
        }
    }
}
