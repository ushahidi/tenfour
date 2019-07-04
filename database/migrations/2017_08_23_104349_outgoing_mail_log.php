<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OutgoingMailLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_mail_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('to');
            $table->string('from');
            $table->string('subject');
            $table->integer('rollcall_id'); // no foreign key here - want to keep log even if rollcall is deleted
            $table->enum('type', array(
                'other', 'rollcall', 'invite', 'verification',
                'FreePromoEnding', 'PaymentFailed', 'PaymentSucceeded',
                'ResetPassword', 'TrialEnding'
            ))->default('other');
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
        Schema::drop('outgoing_mail_log');
    }
}
