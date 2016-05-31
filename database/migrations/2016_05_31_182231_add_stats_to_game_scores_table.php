<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatsToGameScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('games_scores')){
            Schema::table('games_scores', function ($table) {
                $table->integer('team_lob')->default(0);
                $table->decimal('slg',5,3)->default(0);
                $table->decimal('obp',5,3)->default(0);
                $table->decimal('avg',5,3)->default(0);
                $table->decimal('fpct',5,3)->default(0);
                $table->text('officials')->default('');
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
        if(Schema::hasTable('games_scores')){
            Schema::table('games_scores', function ($table) {
                $table->dropColumn('status');
            });
        }
    }
}
