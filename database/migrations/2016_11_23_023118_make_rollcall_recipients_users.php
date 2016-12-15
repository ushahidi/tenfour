<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeRollcallRecipientsUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add new rollcall recipients table to join users to rollcalls
        Schema::create('roll_call_recipients', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->default(0);
            $table->integer('roll_call_id')->unsigned()->default(0);
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('roll_call_id')->references('id')->on('roll_calls')
                ->onDelete('cascade');
        });

        // Rename rollcalls to contacts join table
        // We keep this so we can use it to track messages sent to contacts
        Schema::rename('contact_roll_call', 'roll_call_messages');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('roll_call_messages', 'contact_roll_call');
        Schema::drop('roll_call_recipients');
    }
}
