<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if(!Schema::hasTable('teams')){
            Schema::create('teams', function (Blueprint $table) {
                $table->increments('id');
                $table->string('sr_league_id');
                $table->string('league_name');
                $table->string('league_alias');
                $table->string('sr_division_id');
                $table->string('division_name');
                $table->string('division_alias');
                $table->string('name');
                $table->string('market');
                $table->string('abbr');
                $table->string('sr_team_id');
                $table->integer('venue_id')->unsigned();
                $table->foreign('venue_id')->references('id')->on('venues');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if(Schema::hasTable('teams')){
            Schema::drop('teams');
        }
    }
}
