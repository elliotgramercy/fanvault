<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWinLossFieldsToTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('teams')){
            Schema::table('teams', function ($table) {
                $table->integer('won');
                $table->integer('lost');
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
        if(Schema::hasTable('teams')){
            Schema::table('teams', function ($table) {
                $table->dropColumn('won');
                $table->dropColumn('lost');
            });
        }
    }
}
