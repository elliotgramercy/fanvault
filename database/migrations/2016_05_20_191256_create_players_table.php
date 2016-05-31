<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('players')){
            Schema::create('players', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('team_id')->unsigned();
                $table->foreign('team_id')->references('id')->on('teams');
                $table->string('sr_player_id');
                $table->string('position');
                $table->string('primary_position');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('preferred_name');
                $table->integer('jersey_number');
                $table->integer('pitching_er')->default(0);
                $table->decimal('pitching_era',5,3)->default(0);
                $table->integer('pitching_so')->default(0);
                $table->integer('pitching_bb')->default(0);
                $table->integer('pitching_h')->default(0);
                $table->integer('hitting_h')->default(0);
                $table->integer('hitting_bb')->default(0);
                $table->integer('hitting_so')->default(0);
                $table->decimal('hitting_avg',5,3)->default(0);
                $table->integer('hitting_rbi')->default(0);
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
        if(Schema::hasTable('players')){
            Schema::drop('players');
        }
    }
}
