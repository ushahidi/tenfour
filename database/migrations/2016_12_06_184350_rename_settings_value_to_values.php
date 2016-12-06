<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSettingsValueToValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('settings', function (Blueprint $table) {
          $table->renameColumn('value', 'values');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('settings', function (Blueprint $table) {
          $table->renameColumn('values', 'value');
      });
    }
}
