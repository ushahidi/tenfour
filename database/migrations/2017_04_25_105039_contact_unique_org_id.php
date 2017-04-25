<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ContactUniqueOrgId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->integer('organization_id')->after('user_id')->unsigned()->nullable()->default(null);;
        });

        foreach (DB::table('contacts')->get() as $contact) {
            $org_id = DB::table('users')->where('id', '=', $contact->user_id)->value('organization_id');
            DB::table('contacts')->where('id', $contact->id)->update([
                'organization_id' => $org_id
            ]);
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');;
            $table->dropUnique('contacts_type_contact_unique');
            $table->unique(['type', 'contact', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropUnique('contacts_type_contact_organization_id_unique');
            $table->dropForeign('contacts_organization_id_foreign');
            $table->dropColumn('organization_id');
            $table->unique(['type', 'contact']);
        });
    }
}
