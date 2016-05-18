<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhotoToTailgatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tailgates')){
            Schema::table('tailgates', function ($table) {
                $table->string('photo');
                $table->dateTime('start_time');
                $table->dateTime('end_time');
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
        if(Schema::hasTable('tailgates')){
            Schema::table('tailgates', function ($table) {
                $table->dropColumn('photo');
                $table->dropColumn('start_time');
                $table->dropColumn('end_time');
            });
        }
    }
}
