<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUrlsToVenuesTable extends Migration
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
                $table->string('url');
                $table->string('url_ballpark');
                $table->string('url_tickets');
                $table->string('url_parking');
                $table->string('url_map');
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
                $table->dropColumn('url');
                $table->dropColumn('url_ballpark');
                $table->dropColumn('url_tickets');
                $table->dropColumn('url_parking');
                $table->dropColumn('url_map');
            });
        }
    }
}
