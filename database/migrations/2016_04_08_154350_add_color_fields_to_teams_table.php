<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColorFieldsToTeamsTable extends Migration
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
                $table->string('primary_color');
                $table->string('secondary_color');
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
                $table->string('primary_color');
                $table->string('secondary_color');
            });
        }
    }
}
