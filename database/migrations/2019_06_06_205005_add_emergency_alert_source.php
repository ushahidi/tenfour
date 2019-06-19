<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmergencyAlertSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // I could technically use a lambda for this
        // but cold lambdas are a pain because they are slow to come up,
        // and this would let OSS users setup their own however they like 
        Schema::create('alert_source', function (Blueprint $table) {
            //user friendly name for the service
            $table->string('name');

            //human readable key to identify the service across all systems
            $table->string('source_id');

            $table->primary('source_id');

            /**
             * id of the check in this scheduled check_in group was created from
             */
            $table->string('protocol');//IMAP, POP, HTTP,HTTPS

            /**
             * url, usually to a lambda function that will register our request and 
             * orchestrate the pull from them ->push to us 
             * ( ie: tenfour-alert-subscribe-service )
             */
            $table->string('url');

            /**
             * JSON with auth options
             */
            $table->string('authentication_options'); 

            $table->boolean('enabled');
            $table->text('country');
            $table->text('state');
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
        Schema::drop('alert_source');
    }
}
