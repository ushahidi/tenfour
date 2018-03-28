<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdditionalUserRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('roles')->insert([
            ['name' => 'responder', 'description' => 'Only receive and reply to messages, edit your own info'],
            ['name' => 'author', 'description' => 'Send, receive, respond, view responses, view people, view groups'],
            ['name' => 'viewer', 'description' => 'Receive, respond, view responses, view people, view groups'],
        ]);

        DB::update('update users set role = "responder" where role = "member"');

        //https://github.com/laravel/framework/issues/1186
        DB::statement("ALTER TABLE `users` CHANGE COLUMN `role` `role` varchar(32) COLLATE utf8_unicode_ci DEFAULT 'responder'");

        DB::delete('delete from roles where name = "member"');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('roles')->insert([
            ['name' => 'member', 'description' => 'Only receive and reply to messages, edit your own info'],
        ]);

        DB::statement("ALTER TABLE `users` CHANGE COLUMN `role` `role` varchar(32) COLLATE utf8_unicode_ci DEFAULT 'member'");

        DB::update('update users set role = "member" where role = "responder"');

        DB::delete('delete from roles where name = "author"');
        DB::delete('delete from roles where name = "responder"');
        DB::delete('delete from roles where name = "viewer"');
    }
}
