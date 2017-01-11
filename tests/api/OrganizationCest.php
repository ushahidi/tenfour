<?php

class OrganizationCest
{
    protected $endpoint = '/api/v1/organizations';

    /*
     * Get all organizations as an admin
     *
     */
    public function getAllOrganizations(ApiTester $I)
    {
        $I->wantTo('Get a list of my organizations as an admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'organizations' => [
                [
                    'name'    => 'RollCall',
                    'url'     => 'rollcall',
                    'user' => [
                        'id'   => 5,
                        'role' => 'admin',
                    ]
                ],
            ]
        ]);
    }
    /*
     * Get all organizations as an admin
     *
     */
    public function cannotGetAllOrganizationsWithoutAuth(ApiTester $I)
    {
        $I->wantTo('Can not a list of all organizations with out auth');
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    /*
     * Get all organizations as an admin
     *
     */
    public function getOrganizationByUrl(ApiTester $I)
    {
        $I->wantTo('Get a list of all organizations by url');
        $I->sendGET($this->endpoint  . '?url=testers');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'organizations' => [[
                'name'    => 'Testers',
                'url'     => 'testers'
            ]]
        ]);
    }

    /*
     * List organizations that belong to a user id
     *
     */
    public function filterOrganizationsByUser(ApiTester $I)
    {
        $endpoint = $this->endpoint . '/?user=1';
        $I->wantTo('Get a list of all organizations that a user id belongs to');
        $I->amAuthenticatedAsUser();
        $I->sendGET($endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'organizations' => [
                [
                    'name'    => 'RollCall',
                    'url'     => 'rollcall',
                    'user' => [
                        'id'   => 1,
                        'role' => 'member',
                    ]
                ],
                [
                    'name'    => 'Testers',
                    'url'     => 'testers',
                    'user' => [
                        'id'   => 1,
                        'role' => 'admin',
                    ]
                ]
            ]
        ]);
    }

    /*
     * List organizations that belong to the current user
     *
     */
    public function filterOrganizationsByCurrentUser(ApiTester $I)
    {
        $endpoint = $this->endpoint . '/?user=me';
        $I->wantTo('Get a list of all organizations that the current user belongs to');
        $I->amAuthenticatedAsUser();
        $I->sendGET($endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'name'    => 'RollCall',
                'url'     => 'rollcall',
                'user' => [
                    'id'   => 1,
                    'role' => 'member',
                ]
            ],
            [
                'name'    => 'Testers',
                'url'     => 'testers',
                'user' => [
                    'id'   => 1,
                    'role' => 'admin',
                ]
            ]

        ]);
    }

    /*
     * View organization as an org admin
     *
     */
    public function viewOrganizationAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('view organization as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name'    => 'RollCall',
            'url'     => 'rollcall',
            'user' => [
                'id'   => 4,
                'role' => 'owner',
            ]
        ]);
    }

    /*
     * Create organization as user
     *
     */
    public function createOrganization(ApiTester $I)
    {
        $I->wantTo('Create an organization as user');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'name' => 'Test org',
            'url'  => 'test'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Test org',
            'url' => 'test',
            'user' => [
                'id'   => 1,
                'role' => 'owner',
            ]
        ]);
    }

    /*
     * Update organization details as the organization owner
     *
     */
    public function updateOrganizationAsOrgOwner(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Update organization details as the admin');
        $I->amAuthenticatedAsOrgOwner();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'name' => 'Rollcall Org',
            'url'  => 'rollcall',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Rollcall Org',
            'url'  => 'rollcall',
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
        $I->wantTo('Update organization details as the admin');
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
            'role' => 'owner'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'id'   => 3,
                'role' => 'owner'
            ]
        ]);
    }

    /*
     * Update organization details as a user
     *
     */
    public function updateOrganizationAsUser(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Update organization details as a user');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'name' => 'Rollcall',
            'url'  => 'rollcall',
            'people' => [
                [
                    'id'   => '3',
                    'role' => 'admin'
                ]
            ]
        ]);
        $I->seeResponseCodeIs(403);
    }

    /*
     * Change role as org admin
     *
     */
    public function changeMemberRoleAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Change member role as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'name' => 'Rollcall',
            'url'  => 'rollcall',
            'people' => [
                [
                    'id'   => '3',
                    'role' => 'owner'
                ]
            ]
        ]);
        $I->seeResponseCodeIs(403);
    }

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
            'email' => 'mary@rollcall.io',
            'role'  => 'member',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'email' => 'mary@rollcall.io',
            'role'  => 'member',
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
            'contact' => '077242424',
            'type'    => 'phone',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contact' => [
                'contact' => '077242424',
                'type'    => 'phone',
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
            'contact' => '087242424',
            'type'    => 'phone',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contact' => [
                'contact' => '087242424',
                'type'    => 'phone',
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
        $I->wantTo('Add member contacts as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET($this->endpoint."/$id/people/$user_id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'name' => 'Test user',
                'email' => 'test@ushahidi.com',
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
            'email' => 'mary@rollcall.io',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'person' => [
                'email' => 'mary@rollcall.io',
                'role' => 'member',
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
     * Add admin to an organization as org admin
     *
     */
    public function addAdminAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Add people as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/people", [
            'id'   => 6,
            'role' => 'admin',
        ]);
        $I->seeResponseCodeIs(403);
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
     * Set an organization's setting
     *
     */
    public function setSetting(ApiTester $I)
    {
      $id = 2;
      $I->wantTo('Set an organization\'s setting');
      $I->amAuthenticatedAsOrgOwner();
      $I->haveHttpHeader('Content-Type', 'application/json');
      $I->sendPUT($this->endpoint."/$id", [
          'name' => 'Rollcall Org',
          'url'  => 'rollcall',
          'settings'  => ['channels' => ['email' => true]],
      ]);
      $I->seeResponseCodeIs(200);
      $I->seeResponseIsJson();
      $I->seeResponseContainsJson(["organization" => ["settings" => [
        [
          "key" => 'channels',
          "values" => ['email' => true]
        ],
        [
          "key" => 'organization_types',
          "values" => ['election']
        ]
      ]]]);
    }


    /*
     * Delete organization as an org owner
     *
     */
    public function deleteOrganization(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Delete an organization');
        $I->amAuthenticatedAsOrgOwner();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall',
        ]);
    }
}
