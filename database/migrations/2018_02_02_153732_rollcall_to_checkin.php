<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RollcallToCheckin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('roll_calls', 'check_ins');
        Schema::rename('roll_call_messages', 'check_in_messages');
        Schema::rename('roll_call_recipients', 'check_in_recipients');

        // can't rename columns in the 'check_ins' table
        // https://stackoverflow.com/questions/33140860/laravel-5-1-unknown-database-type-enum-requested
        // so delete all self test check_ins and add a new column
        DB::statement('delete from check_ins where self_test_roll_call = 1');

        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropColumn('self_test_roll_call');
            $table->boolean('self_test_check_in')->default(false);
        });

        Schema::table('outgoing_mail_log', function (Blueprint $table) {
            $table->renameColumn('rollcall_id', 'check_in_id');
        });

        Schema::table('outgoing_sms_log', function (Blueprint $table) {
            $table->renameColumn('rollcall_id', 'check_in_id');
        });

        Schema::table('replies', function (Blueprint $table) {
            $table->renameColumn('roll_call_id', 'check_in_id');
        });

        Schema::table('check_in_messages', function (Blueprint $table) {
            $table->renameColumn('roll_call_id', 'check_in_id');
        });

        // messy workaround for
        // https://stackoverflow.com/questions/33140860/laravel-5-1-unknown-database-type-enum-requested

        Schema::table('check_in_recipients', function (Blueprint $table) {
            $table->text('response_status_tmp');
        });

        DB::statement('update check_in_recipients set response_status_tmp = response_status');
        DB::statement('alter table check_in_recipients drop column response_status');

        Schema::table('check_in_recipients', function (Blueprint $table) {
            $table->renameColumn('roll_call_id', 'check_in_id');
            $table->enum('response_status', ['waiting', 'unresponsive', 'replied']);
        });

        DB::statement('update check_in_recipients set response_status = response_status_tmp');

        Schema::table('check_in_recipients', function (Blueprint $table) {
            $table->dropColumn('response_status_tmp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // messy workaround for
        // https://stackoverflow.com/questions/33140860/laravel-5-1-unknown-database-type-enum-requested

        Schema::table('check_in_recipients', function (Blueprint $table) {
            $table->text('response_status_tmp');
        });

        DB::statement('update check_in_recipients set response_status_tmp = response_status');
        DB::statement('alter table check_in_recipients drop column response_status');

        Schema::table('check_in_recipients', function (Blueprint $table) {
            $table->renameColumn('check_in_id', 'roll_call_id');
            $table->enum('response_status', ['waiting', 'unresponsive', 'replied']);
        });

        DB::statement('update check_in_recipients set response_status = response_status_tmp');

        Schema::table('check_in_recipients', function (Blueprint $table) {
            $table->dropColumn('response_status_tmp');
        });

        // end messy workaround

        Schema::table('check_in_messages', function (Blueprint $table) {
          $table->renameColumn('check_in_id', 'roll_call_id');
        });

        Schema::table('replies', function (Blueprint $table) {
            $table->renameColumn('check_in_id', 'roll_call_id');
        });

        Schema::table('outgoing_mail_log', function (Blueprint $table) {
            $table->renameColumn('check_in_id', 'rollcall_id');
        });

        Schema::table('outgoing_sms_log', function (Blueprint $table) {
            $table->renameColumn('check_in_id', 'rollcall_id');
        });

        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropColumn('self_test_check_in');
            $table->boolean('self_test_roll_call')->default(false);
        });

        Schema::rename('check_in_recipients', 'roll_call_recipients');
        Schema::rename('check_in_messages', 'roll_call_messages');
        Schema::rename('check_ins', 'roll_calls');
    }
}
