<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVenuesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if(!Schema::hasTable('venues')){
            Schema::create('venues', function (Blueprint $table) {
                $table->increments('id');
                $table->string('sr_venue_id');
                $table->string('name');
                $table->string('market');
                $table->integer('capacity');
                $table->string('surface');
                $table->string('address');
                $table->string('city');
                $table->string('state');
                $table->string('zip');
                $table->string('country');
                $table->string('distances');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if(Schema::hasTable('venues')){
            Schema::drop('venues');
        }
    }
}
