<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetUsersOrgAndRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $orgUsers = DB::table('organization_user')->get();

        foreach ($orgUsers as $orgUser) {
            DB::table('users')->where('id', $orgUser->user_id)->update([
                'role' => $orgUser->role,
                'organization_id' => $orgUser->organization_id
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $users = DB::table('users')->whereNotNull('organization_id')->get();

        DB::table('organization_user')->insert(
            array_map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'organization_id' => $user->organization_id,
                    'role' => $user->role
                ];
            }, $users->toArray())
        );
    }
}
