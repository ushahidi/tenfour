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
            'role'  => 'member',
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
            'role' => 'member',
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
        $I->wantTo('Add member contact email as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/people/$user_id/contacts", [
            'contact' => 'mary@example.com',
            'type'    => 'email',
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
     * Add member contact phone to an organization as org admin
     *
     */
    public function addMemberContactPhoneAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $I->wantTo('Add member contact phone as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/people/$user_id/contacts", [
            'contact' => '+1 (207) 7200713',
            'type'    => 'phone',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contact' => [
                'contact' => '+1 (207) 7200713',
                'type'    => 'phone',
            ]
        ]);
    }

    /*
     * Ensure that default role for new member is 'member' if unspecified
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
            'role' => 'member',
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
     * Update member contact
     *
     */
    public function updateMemberContactAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $contact_id = 4;
        $I->wantTo('Update member contact as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id/people/$user_id/contacts/$contact_id", [
            'contact' => '+12077200713',
            'type'    => 'phone',
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
                'contact' => '0792999999',
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
                        'contact' => '0721674180',
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
                'role' => 'member'
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
                'role'  => 'member'
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
                    'role' => 'member',
                    'name' => 'Test user',
                    'initials' => 'TU'
                ],
                [
                    'id' => 3,
                    'role' => 'member',
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
                    'role' => 'member',
                    'name' => 'Test user',
                    'initials' => 'TU'
                ],
                [
                    'id' => 3,
                    'role' => 'member',
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
                'role' => 'member',
                'person_type' => 'member',
                'invite_sent' => true
            ]
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
                'role' => 'member',
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
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $record = $I->grabRecord('password_resets', array('email' => 'test@ushahidi.com'));
        $I->sendPOST('/password/reset', [
            'username' => 'test@ushahidi.com',
            'password' => 'cake1234',
            'password_confirmation' => 'cake1234',
            'token' => $record['token']
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->sendPOST('/oauth/access_token', [
            'client_id' => 'webapp',
            'client_secret' => 'secret',
            'scope' => 'user',
            'username' => 'test@ushahidi.com',
            'password' => 'cake1234',
            'grant_type' => 'password'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    /**
     * Unsubscribe from rollcall emails
     */
    public function unsubscribe(ApiTester $I)
    {
        $I->wantTo('Unsubscribe from emails');
        $I->seeInDatabase('contacts', array('contact' => 'test@ushahidi.com', 'subscribed' => 1));
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST("unsubscribe", [
            'token' => 'testunsubscribetoken',
            'email' => 'test@ushahidi.com',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeInDatabase('contacts', array('contact' => 'test@ushahidi.com', 'subscribed' => 0));
    }

}
