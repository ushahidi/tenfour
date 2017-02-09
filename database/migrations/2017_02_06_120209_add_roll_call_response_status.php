<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRollCallResponseStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roll_call_recipients', function (Blueprint $table) {
            $table->enum('response_status', ['waiting', 'unresponsive', 'replied']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roll_call_recipients', function (Blueprint $table) {
            $table->dropColumn('response_status');
        });
    }
}
