<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OrganizationSubdomain extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('organizations', function (Blueprint $table) {
        $table->renameColumn('url', 'subdomain');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('organizations', function (Blueprint $table) {
        $table->renameColumn('subdomain', 'url');
      });
    }
}
