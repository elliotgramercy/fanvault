<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoordinatesToVenues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('venues')){
            Schema::table('venues', function ($table) {
                $table->decimal('lng', 10, 7);
                $table->decimal('lat', 10, 7);
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
        if(Schema::hasTable('venues')){
            Schema::table('venues', function ($table) {
                $table->dropColumn('lng', 10, 7);
                $table->dropColumn('lat', 10, 7);
            });
        }
    }
}
