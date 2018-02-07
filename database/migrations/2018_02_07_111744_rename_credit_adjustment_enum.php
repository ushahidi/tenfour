<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCreditAdjustmentEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // messy workaround for
        // https://stackoverflow.com/questions/33140860/laravel-5-1-unknown-database-type-enum-requested

        Schema::table('credit_adjustments', function (Blueprint $table) {
            $table->string('type_tmp', 20)->default('misc');
        });

        DB::statement('update credit_adjustments set type_tmp = type');
        DB::statement('alter table credit_adjustments drop column type');

        Schema::table('credit_adjustments', function (Blueprint $table) {
            $table->renameColumn('type_tmp', 'type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // not needed
    }
}
