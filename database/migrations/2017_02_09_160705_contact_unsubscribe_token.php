<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

class ContactUnsubscribeToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('contacts', function (Blueprint $table) {
             $table->string('unsubscribe_token')->nullable();
         });

         foreach (DB::table('contacts')->get() as $contact) {
           $unsubscribe_token = Hash::Make(config('app.key'));

           DB::table('contacts')->where('id', $contact->id)->update([
             'unsubscribe_token' => $unsubscribe_token
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
         Schema::table('contacts', function (Blueprint $table) {
             $table->dropColumn('unsubscribe_token');
         });
    }
}
