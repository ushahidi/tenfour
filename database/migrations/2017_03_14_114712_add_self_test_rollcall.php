<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSelfTestRollcall extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roll_calls', function (Blueprint $table) {
           $table->boolean('self_test_roll_call')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roll_calls', function (Blueprint $table) {
            $table->dropColumn('self_test_roll_call');
        });
    }
}
