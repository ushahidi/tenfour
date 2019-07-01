<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmergencyAlertSubscription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //who are the groups or users subscribed to get the emergency_alert?
        Schema::create('alert_subscription', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('feed_id');
            $table->unsignedBigInteger('checkin_template_id');
            $table->foreign('feed_id')
                ->references('id')->on('alert_feed')
                ->onDelete('cascade');
            $table->foreign('checkin_template_id')
                ->references('id')->on('check_ins')
                ->onDelete('cascade');
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
        Schema::drop('alert_subscription');
    }
}
