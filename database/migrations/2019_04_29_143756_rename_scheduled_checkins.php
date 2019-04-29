<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameScheduledCheckins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduled_check_in', function(Blueprint $table) {
            $table->dropForeign('scheduled_check_in_check_ins_id_foreign');
            $table->dropColumn('check_ins_id');
        });

        Schema::rename('scheduled_check_in', 'scheduled_checkin');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //noop
    }
}
