<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCheckinsFieldScheduled extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('alter table check_ins CHANGE scheduled_check_in_id scheduled_checkin_id bigint(20) unsigned DEFAULT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('alter table check_ins CHANGE scheduled_checkin_id scheduled_check_in_id bigint(20) unsigned DEFAULT NULL;');
    }
}
