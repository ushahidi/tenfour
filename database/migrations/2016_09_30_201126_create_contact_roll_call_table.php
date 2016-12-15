<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactRollCallTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_roll_call', function (Blueprint $table) {
            $table->integer('contact_id')->unsigned()->default(0);
            $table->integer('roll_call_id')->unsigned()->default(0);
            $table->foreign('contact_id')->references('id')->on('contacts')
                ->onDelete('cascade');
            $table->foreign('roll_call_id')->references('id')->on('roll_calls')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contact_roll_call');
    }
}
