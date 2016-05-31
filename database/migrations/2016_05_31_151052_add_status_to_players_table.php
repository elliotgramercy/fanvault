<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToPlayersTable extends Migration
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
                $table->enum('status',array('A','I'))->default('A');
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
                $table->dropColumn('status');
            });
        }
    }
}
