<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrivateToUserGameImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('user_game_images')){
            Schema::table('user_game_images', function ($table) {
                $table->boolean('private')->default(true);
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
        if(Schema::hasTable('user_game_images')){
            Schema::table('user_game_images', function ($table) {
                $table->dropColumn('private');
            });
        }
    }
}
