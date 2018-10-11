<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CheckinMessagesChannel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_in_messages', function (Blueprint $table) {
            $table->string('to')->nullable();
            $table->string('channel', 16)->nullable();
            $table->integer('credits')->unsigned()->nullable();
            $table->integer('credit_adjustment_id')->unsigned()->nullable();
            $table->foreign('credit_adjustment_id')
                ->references('id')->on('credit_adjustments')
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('to');
            $table->dropColumn('channel');
            $table->dropColumn('credits');
            $table->dropColumn('credit_adjustment_id');
        });
    }
}
