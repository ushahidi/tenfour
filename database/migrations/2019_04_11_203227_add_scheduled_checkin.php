<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScheduledCheckin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('scheduled_check_in', function (Blueprint $table) {
            $table->integer('check_ins_id')->unsigned()->default(0);
            $table->foreign('check_ins_id')
                ->references('id')->on('check_ins')
                ->onDelete('cascade');
            $table->boolean('scheduled')->default(false);
            // frequency (string) - how often the checkin will be sent with possible options:
            // once, hourly, daily, weekly, biweekly, monthly
            $table->string('frequency')->default('once');
            // started_at (date) - the start date for the first checkin to be sent
            $table->date('starts_at');
            // expired_at (date) - the finish date for the last checkin will be sent
            $table->date('expires_at');
            // remaining_count (integer) - the number of remaining checkins to be sent before it expires
            $table->integer('remaining_count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
