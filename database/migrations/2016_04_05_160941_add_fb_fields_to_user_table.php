<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFbFieldsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('users')){
            Schema::table('users', function ($table) {
                $table->string('first_name');
                $table->string('last_name');
                $table->dateTime('dob');
                $table->enum('gender',array('male','female'));
                $table->bigInteger('fb_user_id')->unsigned();
                $table->string('fb_auth_tok');
                $table->text('photo');
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
        if(Schema::hasTable('users')){
            Schema::table('users', function ($table) {
                $table->dropColumn('first_name');
                $table->dropColumn('last_name');
                $table->dropColumn('dob');
                $table->dropColumn('gender');
                $table->dropColumn('fb_user_id');
                $table->dropColumn('fb_auth_tok');
                $table->dropColumn('photo');
            });
        }
    }
}
