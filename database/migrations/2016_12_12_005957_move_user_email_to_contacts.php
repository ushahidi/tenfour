<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveUserEmailToContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = DB::table('users')
            ->whereNotIn('email', DB::table('contacts')
                ->select('contact')
                ->where('type', '=', 'email')
                ->where('user_id', '=', DB::raw('users.id'))
            );

        $users = $users->get();

        DB::table('contacts')->insert(
            array_map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'contact' => $user->email,
                    'can_receive' => true,
                    'type' => 'email',
                    'created_at' => DB::raw('NOW()'),
                    'updated_at' => DB::raw('NOW()')
                ];
            }, $users->toArray())
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $contacts = DB::table('contacts')
            ->where('type', 'email')
            ->distinct('user_id')
            ->get();

        foreach ($contacts as $contact) {
            DB::table('users')
                ->where('id', $contact->user_id)
                ->update(['email' => $contact->contact]);
        }
    }
}
