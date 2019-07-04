<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrepRolesTableForOrgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Wipe existing roles
        DB::table('roles')->truncate();

        // Add roles for the app
        DB::table('roles')->insert([
            ['name' => 'owner', 'description' => 'Administrative user, has access to everything, cannot be deleted'],
            ['name' => 'admin', 'description' => 'Administrative user, has access to everything'],
            ['name' => 'member', 'description' => 'Respond Only, only receive and reply to messages, edit your own info'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing
    }
}
