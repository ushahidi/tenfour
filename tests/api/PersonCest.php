<?php

class PersonCest
{
    protected $endpoint = '/api/v1/organizations';

    /*
     * Add member to an organization as org admin
     *
     */
    public function addMemberAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Add member as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/people", [
            'name' => 'Mary Mata',
            'role'  => 'responder',
            'password' => 'dancer01',
            'password_confirm' => 'dancer01',
            'person_type' => 'user',
            'config_profile_reviewed' => true,
            'config_self_test_sent' => false
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Mary Mata',
            'role' => 'responder',
            'person_type' => 'user',
            'config_profile_reviewed' => true,
            'config_self_test_sent' => false,
            'organization_id' => 2
        ]);
    }

    /*
     * Add member contact email to an organization as org admin
     *
     */
    public function addMemberContactEmailAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $org_id = 2;
        $I->wantTo('Add member contact email as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/people/$user_id/contacts", [
            'contact' => 'mary@example.com',
            'type'    => 'email',
            'organization_id' => $org_id,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contact' => [
                'contact' => 'mary@example.com',
                'type'    => 'email',
            ]
        ]);
    }

    /*
     * Add duplicate contact email to an organization as org admin
     *
     */
    public function addDuplicateContactEmailAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $org_id = 2;
        $I->wantTo('Add duplicate member contact email as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/people/$user_id/contacts", [
            'contact' => 'mary@example.com',
            'type'    => 'email',
            'organization_id' => $org_id,
        ]);
        $I->seeResponseCodeIs(200);
        $I->sendPOST($this->endpoint."/$id/people/$user_id/contacts", [
            'contact' => 'mary@example.com',
            'type'    => 'email',
            'organization_id' => $org_id,
        ]);
        $I->seeResponseCodeIs(422);
    }

    /*
     * Add duplicate contact email from another organization as org admin
     *
     */
    public function addDuplicateContactEmailAnotherOrgAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $org_id = 2;
        $I->wantTo('Add duplicate member contact email from another organization as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/people/$user_id/contacts", [
            'contact' => 'test+contact2@organization2.com',
            'type'    => 'email',
            'organization_id' => $org_id,
        ]);
        $I->seeResponseCodeIs(200);
    }

    /*
     * Add member contact phone to an organization as org admin
     *
     */
    public function addMemberContactPhoneAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $org_id = 2;
        $I->wantTo('Add member contact phone as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/people/$user_id/contacts", [
            'contact' => '+1 (207) 7200713',
            'type'    => 'phone',
            'organization_id' => $org_id,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contact' => [
                'contact' => '+12077200713',
                'type'    => 'phone',
                'meta'    => [
                    'country_code'    => '1',
                    'national_number' => '2077200713',
                ]
            ]
        ]);
    }

    /*
     * Add invalid phone number
     *
     */
    public function addInvalidPhoneNumber(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $org_id = 2;
        $I->wantTo('Add invalid phone number');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/people/$user_id/contacts", [
            'contact' => 'Invalid phone',
            'type'    => 'phone',
            'organization_id' => $org_id,
        ]);
        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
    }


    /*
     * Ensure that default role for new member is 'responder' if unspecified
     *
     */
    public function addMemberWithUnspecifiedRole(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Add member with unspecified role');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST($this->endpoint."/$id/people", [
            'name' => 'Mary Mata',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Mary Mata',
            'role' => 'responder',
        ]);
    }

    /*
     * Update Member
     *
     */
    public function updateOrganizationMemberAsOrgOwner(ApiTester $I)
    {
        $id = 2;
        $user_id = 3;
        $I->wantTo('Update organization member as the admin');
        $I->amAuthenticatedAsOrgOwner();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id/people/$user_id", [
            'name' => 'Updated org member',
            'role' => 'admin'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'id'   => 3,
                'name' => 'Updated org member',
                'role' => 'admin'
            ]
        ]);
    }

    /*
     * Update Member
     *
     */
    public function updateOrganizationMemberAsMember(ApiTester $I)
    {
        $org_id = 2;
        $user_id = 1;
        $I->wantTo('Update an organization member as the member');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$org_id/people/$user_id", [
            'name' => 'Updated org member',
            'password' => 'rollcall',
            'person_type' => 'user'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'id'   => $user_id,
                'name' => 'Updated org member',
                'person_type' => 'user'
            ]
        ]);
    }

    /*
     * Update Member role as member
     *
     */
    public function updateMemberRoleAsMember(ApiTester $I)
    {
        $org_id = 2;
        $user_id = 1;
        $I->wantTo('Update a member\'s role as the member');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$org_id/people/$user_id", [
            'name' => 'Updated org member',
            'role' => 'admin'
        ]);
        $I->seeResponseCodeIs(403);
    }

    /*
     * Update groups as member
     *
     */
    public function updateMemberGroups(ApiTester $I)
    {
        $org_id = 2;
        $user_id = 2;
        $I->wantTo('Update a member\'s groups');
        $I->amAuthenticatedAsOrgOwner();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$org_id/people/$user_id", [
            'name' => 'Updated org member',
            'groups' => [
              [ 'id' => 1 ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->sendGET($this->endpoint."/$org_id/people/$user_id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'groups' => [
              [ 'id' => 1 ],
            ]
        ]);
        $I->dontSeeResponseContainsJson([
            'groups' => [
              [ 'id' => 2 ],
            ]
        ]);
    }

    /*
     * Update member contact
     *
     */
    public function updateMemberContactAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $contact_id = 4;
        $org_id = 2;
        $I->wantTo('Update member contact as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id/people/$user_id/contacts/$contact_id", [
            'contact' => '+12077200713',
            'type'    => 'phone',
            'organization_id' => $org_id,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contact' => [
                'contact' => '+12077200713',
                'type'    => 'phone',
            ]
        ]);
    }

    /*
     * Transfer org ownership as org owner
     *
     */
    public function transferOrgOwnershipAsOrgOwner(ApiTester $I)
    {
        $id = 2;
        $user_id = 3;
        $I->wantTo('Transfer org ownership');
        $I->amAuthenticatedAsOrgOwner();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id/people/$user_id", [
            'name' => 'Org Member',
            'role' => 'owner'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'id'   => 3,
                'name' => 'Org Member',
                'role' => 'owner'
            ]
        ]);
    }

    /*
     * Delete member contact
     *
     */
    public function deleteMemberContactAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $contact_id = 4;
        $I->wantTo('Delete member contact org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id/people/$user_id/contacts/$contact_id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contact' => [
                'contact' => '+254792999999',
                'type'    => 'phone',
            ]
        ]);
    }

    /*
     * Get organization member
     *
     */
    public function getMemberAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $I->wantTo('Get member as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET($this->endpoint."/$id/people/$user_id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'name' => 'Test user',
                'contacts' => [
                    [
                        'contact' => '+254721674180',
                        'type'    => 'phone',
                        'replies' => [
                            [
                                'message' => 'I am OK'
                            ]
                        ]
                    ],
                    [
                        'contact' => 'test@ushahidi.com',
                        'type'    => 'email',
                    ]
                ],
                'rollcalls' => [
                    [
                        'message' => 'Another test roll call'
                    ]
                ],
                'role' => 'responder'
            ]
        ]);
    }

    /*
     * Get organization member
     *
     */
    public function getMemberAsMember(ApiTester $I)
    {
        $org_id = 2;
        $user_id = 1;
        $I->wantTo('Get member as a member');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET($this->endpoint."/$org_id/people/$user_id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'id'    => $user_id,
                'name'  => 'Test user',
                'role'  => 'responder'
            ]
        ]);
    }

    /*
     * Get user details as 'me'
     *
     */
    public function getUserAsMe(ApiTester $I)
    {
        $org_id = 2;
        $user_id = 'me';
        $I->wantTo('Get my own user details as a user');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint."/$org_id/people/$user_id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'name'        => 'Test user',
                'person_type' => 'user'
            ]
        ]);
    }

    /*
     * Delete member from an organization as org admin
     *
     */
    public function deleteMemberAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 3;
        $I->wantTo('Delete member as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id/people/$user_id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'id'   => 3,
                'name' => 'Org member'
            ]
        ]);
    }

    /*
     * Delete owner from an organization as org admin
     *
     */
    public function deleteOwnerAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 4;
        $I->wantTo('Delete owner as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id/people/$user_id");
        $I->seeResponseCodeIs(403);
    }

    /*
     * Delete owner from an organization as org owner
     *
     */
    public function deleteOrgOwnerAsOrgOwner(ApiTester $I)
    {
        $id = 2;
        $user_id = 4;
        $I->wantTo('Delete owner as an org owner');
        $I->amAuthenticatedAsOrgOwner();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id/people/$user_id");
        $I->seeResponseCodeIs(403);
    }

    /*
     * Delete owner from an organization as org owner
     *
     */
    public function deleteOrgAdminAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 2;
        $I->wantTo('Delete org admin as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id/people/$user_id");
        $I->seeResponseCodeIs(200);
    }

    /*
     * Delete member from an organization as member
     *
     */
    public function deleteMemberAsMember(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $I->wantTo('Delete member as a member');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id/people/$user_id");
        $I->seeResponseCodeIs(200);
    }

    /*
     * List people in an organization
     *
     */
    public function listMembersAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('List people of an organization as org Admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET($this->endpoint."/$id/people");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'people' => [
                [
                    'id' => 4,
                    'role' => 'owner',
                    'name' => 'Org owner',
                    'initials' => 'OO'
                ],
                [
                    'id' => 5,
                    'role' => 'admin',
                    'name' => 'Org admin',
                    'initials' => 'OA'
                ],
                [
                    'id' => 1,
                    'role' => 'responder',
                    'name' => 'Test user',
                    'initials' => 'TU'
                ],
                [
                    'id' => 3,
                    'role' => 'responder',
                    'name' => 'Org member',
                    'initials' => 'OM'
                ]
            ]
        ]);
    }

    /*
     * List people in an organization
     *
     */
    public function listMembersAsOrgMember(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('List people of an organization as org Admin');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET($this->endpoint."/$id/people");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'people' => [
                [
                    'id' => 4,
                    'role' => 'owner',
                    'name' => 'Org owner',
                    'initials' => 'OO'
                ],
                [
                    'id' => 5,
                    'role' => 'admin',
                    'name' => 'Org admin',
                    'initials' => 'OA'
                ],
                [
                    'id' => 1,
                    'role' => 'responder',
                    'name' => 'Test user',
                    'initials' => 'TU'
                ],
                [
                    'id' => 3,
                    'role' => 'responder',
                    'name' => 'Org member',
                    'initials' => 'OM'
                ]
            ]
        ]);
    }


    /**
     * Send an invite
     */
    public function sendInvite(ApiTester $I)
    {
        $org_id = 2;
        $user_id = 6;
        $I->wantTo('Send an invite to join an organization');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$org_id/people/$user_id/invite");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'name' => 'Org member 2',
                'description' => 'Org Member 2',
                'role' => 'responder',
                'person_type' => 'member',
                'invite_sent' => true
            ]
        ]);

        $I->seeRecord('outgoing_mail_log', [
            'subject'     => "RollCall invited you to join Rollcall",
            'type'        => 'invite',
            'to'          => 'org_member2@ushahidi.com',
        ]);
    }

    /**
     * Accept an invite
     */
    public function acceptInvite(ApiTester $I)
    {
        $org_id = 2;
        $user_id = 6;
        $I->wantTo('Accept an invite to join an organization');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST("invite/$org_id/accept/$user_id", [
            'invite_token' => 'asupersecrettoken',
            'password' => 'abcd1234'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'name' => 'Org member 2',
                'description' => 'Org Member 2',
                'role' => 'responder',
                'person_type' => 'user'
            ]
        ]);
    }


    /*
     * Update a user w/o specifying role
     *
     */
    public function updateUserDoesntChangeRole(ApiTester $I)
    {
        $orgId = 2;
        $id = 4;
        $I->wantTo('Update a person without specifying role');
        $I->amAuthenticatedAsOrgOwner();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$orgId/people/$id", [
            'name' => 'Update!',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'id' => 4,
                'role' => 'owner',
                'person_type' => 'user'
            ]
        ]);
    }

    /*
     * Change a user's password
     *
     */
    public function changePassword(ApiTester $I)
    {
        $orgId = 2;
        $id = 1;
        $I->wantTo('Change a user\'s pasword');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$orgId/people/$id", [
            'name' => 'Mary Mata',
            'password' => 'another_password',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'id' => 1,
                'name' => 'Mary Mata'
            ]
        ]);

        $I->sendPOST('/oauth/access_token', [
            'client_id' => 'webapp',
            'client_secret' => 'secret',
            'scope' => 'user',
            'username' => 'test@ushahidi.com',
            'subdomain' => 'rollcall',
            'password' => 'another_password',
            'grant_type' => 'password'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    /*
     * Reset password
     *
     */
    public function resetPassword(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Reset my password');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/password/email', [
            'username' => 'test@ushahidi.com',
            'subdomain' => 'rollcall'
        ]);
        $I->seeResponseCodeIs(200);

        $record = $I->grabRecord('password_resets', array('email' => 'test@ushahidi.com'));
        $I->sendPOST('/password/reset', [
            'username' => 'test@ushahidi.com',
            'password' => 'cake1234',
            'password_confirmation' => 'cake1234',
            'subdomain' => 'rollcall',
            'token' => $record['token']
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendPOST('/oauth/access_token', [
            'client_id' => 'webapp',
            'client_secret' => 'secret',
            'scope' => 'user',
            'username' => 'test@ushahidi.com',
            'password' => 'cake1234',
            'subdomain' => 'rollcall',
            'grant_type' => 'password'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeRecord('outgoing_mail_log', [
            'subject'     => "Reset Password",
            'type'        => 'ResetPassword',
            'to'          => 'test@ushahidi.com',
        ]);
    }

    /**
     * Unsubscribe from rollcall emails
     */
    public function unsubscribe(ApiTester $I)
    {
        $I->wantTo('Unsubscribe from emails');
        $I->seeInDatabase('contacts', array('contact' => 'test@ushahidi.com', 'blocked' => 0));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST("unsubscribe", [
            'token' => 'testunsubscribetoken',
            'email' => 'test@ushahidi.com',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeInDatabase('contacts', array('contact' => 'test@ushahidi.com', 'blocked' => 1));

        $I->seeRecord('notifications', [
            'notifiable_id'           => '4',
            'notifiable_type'         => 'RollCall\Models\User',
            'type'                    => 'RollCall\Notifications\Unsubscribe',
            'data'                    => '{"person_name":"Test user","person_id":1,"profile_picture":false,"initials":"TU","contact":"test@ushahidi.com","contact_type":"email"}'
        ]);

    }

    public function notifyOwner(ApiTester $I)
    {
        $orgId = 2;
        $I->wantTo('Send a notification to the owner of the organization');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$orgId/people/owner/notify", [
            'message' => 'send_more_bees',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeRecord('notifications', [
            'notifiable_id'           => '4',
            'notifiable_type'         => 'RollCall\Models\User',
            'type'                    => 'RollCall\Notifications\PersonToPerson',
            'data'                    => '{"message":"send_more_bees","person_name":"Test user","person_id":1,"profile_picture":false,"initials":"TU"}'
        ]);
    }

}
