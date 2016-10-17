<?php

class OrganizationCest
{
    protected $endpoint = '/organizations';

    /*
     * Get all organizations as an admin
     *
     */
    public function getAllOrganizations(ApiTester $I)
    {
        $I->wantTo('Get a list of all organizations as an admin');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'name'    => 'RollCall',
                'url'     => 'rollcall.rollcall.io',
                'members' => [
                    [
                        'id'   => 4,
                        'role' => 'owner',
                    ]
                ]
            ],
            [
                'name'    => 'Testers',
                'url'     => 'testers.rollcall.io',
                'members' => [
                    [
                        'id'   => 4,
                        'role' => 'owner',
                    ]
                ]
            ]
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
            [
                'name'    => 'RollCall',
                'url'     => 'rollcall.rollcall.io',
                'members' => [
                    [
                        'id'   => 1,
                        'role' => 'member',
                    ]
                ]
            ],
            [
                'name'    => 'Testers',
                'url'     => 'testers.rollcall.io',
                'members' => [
                    [
                        'id'   => 1,
                        'role' => 'admin',
                    ]
                ]
            ]

        ]);
    }

    /*
     * List organizations that a user belong to the current user
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
                'url'     => 'rollcall.rollcall.io',
                'members' => [
                    [
                        'id'   => 1,
                        'role' => 'member',
                    ]
                ]
            ],
            [
                'name'    => 'Testers',
                'url'     => 'testers.rollcall.io',
                'members' => [
                    [
                        'id'   => 1,
                        'role' => 'admin',
                    ]
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
            'url'     => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => 4,
                    'role' => 'owner',
                ]
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
            'url' => 'test.rollcall.io'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Test org',
            'url' => 'test.rollcall.io',
            'members' => [
                [
                    'id'   => 1,
                    'role' => 'owner',
                ]
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
            'url'  => 'rollcall.rollcall.io',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Rollcall Org',
            'url'  => 'rollcall.rollcall.io',
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
        $I->sendPUT($this->endpoint."/$id/members/$user_id", [
            'role' => 'admin'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => 3,
                    'role' => 'admin'
                ]
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
        $I->sendPUT($this->endpoint."/$id/members/$user_id", [
            'role' => 'owner'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => 3,
                    'role' => 'owner'
                ]
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
            'url'  => 'rollcall.rollcall.io',
            'members' => [
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
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => '3',
                    'role' => 'owner'
                ]
            ]
        ]);
        $I->seeResponseCodeIs(403);
    }

    /*
     * Add members to an organization as org admin
     *
     */
    public function addMembersAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Add members as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/members", [
            [
                'email' => 'mary@rollcall.io',
                'role'  => 'member',
            ],
            [
                'email' => 'jack@rollcall.io',
                'role'  => 'member',
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'email' => 'mary@rollcall.io',
                    'role' => 'member',
                ],
                [
                    'email' => 'jack@rollcall.io',
                    'role' => 'member',
                ]
            ]
        ]);
    }

    /*
     * Add member contacts to an organization as org admin
     *
     */
    public function addMemberContactsAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $I->wantTo('Add member contacts as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/members/$user_id/contacts", [
            [
                'contact' => 'mary@example.com',
                'type'    => 'email',
            ],
            [
                'contact' => '077242424',
                'type'    => 'phone',
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'       => 1,
                    'contacts' => [
                        [
                            'contact' => 'mary@example.com',
                            'type'    => 'email',
                        ],
                        [
                            'contact' => '077242424',
                            'type'    => 'phone',
                        ]
                    ]
                ]
            ]
        ]);
    }

    /*
     * Add member contacts to an organization as org admin
     *
     */
    public function getMemberContactsAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $user_id = 1;
        $I->wantTo('Add member contacts as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET($this->endpoint."/$id/members/$user_id/contacts");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'       => 1,
                    'contacts' => [
                        [
                            'contact' => '0721674180',
                            'type'    => 'phone',
                        ],
                        [
                            'contact' => 'test@ushahidi.com',
                            'type'    => 'email',
                        ]
                    ]
                ]
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
        $I->sendPOST($this->endpoint."/$id/members", [
            'email' => 'mary@rollcall.io',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'email' => 'mary@rollcall.io',
                    'role' => 'member',
                ]
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
        $I->sendDelete($this->endpoint."/$id/members/$user_id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => 3,
                    'name' => 'Org member'
                ]
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
        $I->sendDelete($this->endpoint."/$id/members/$user_id");
        $I->seeResponseCodeIs(403);
    }

    /*
     * Add admin to an organization as org admin
     *
     */
    public function addAdminAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Add members as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/members", [
            'id'   => 6,
            'role' => 'admin',
        ]);
        $I->seeResponseCodeIs(403);
    }

    /*
     * List members in an organization
     *
     */
    public function listMembersAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('List members of an organization as org Admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET($this->endpoint."/$id/members");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id' => 4,
                    'role' => 'owner',
                    'name' => 'Org owner',
                ],
                [
                    'id' => 5,
                    'role' => 'admin',
                    'name' => 'Org admin',
                ],
                [
                    'id' => 1,
                    'role' => 'member',
                    'name' => 'Test user',
                ],
                [
                    'id' => 3,
                    'role' => 'member',
                    'name' => 'Org member',
                ]
            ]
        ]);
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
            'url'  => 'rollcall.rollcall.io',
        ]);
    }
}
