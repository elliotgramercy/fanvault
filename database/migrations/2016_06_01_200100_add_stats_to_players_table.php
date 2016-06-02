<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatsToPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('players')){
            Schema::table('players', function ($table) {
                $table->integer('pitching_win')->default(0);
                $table->integer('pitching_loss')->default(0);
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
            Schema::table('players', function ($table) {
                $table->dropColumn('pitching_win');
                $table->dropColumn('pitching_loss');
            });
        }
    }
}
