<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OutgoingSmsLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_sms_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('to');
            $table->string('from');
            $table->string('driver');
            $table->integer('rollcall_id'); // no foreign key here - want to keep log even if rollcall is deleted
            $table->enum('type', array(
                'other', 'rollcall', 'rollcall_url', 'reminder', 'response_received'
            ))->default('other');
            $table->text('message');
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
        Schema::drop('outgoing_sms_log');
    }
}
