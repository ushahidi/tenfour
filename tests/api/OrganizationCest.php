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
                'name'    => 'Ushahidi',
                'url'     => 'ushahidi.rollcall.io',
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
     * List organizations that a user belongs to
     *
     */
    public function filterOrganizationsByUser(ApiTester $I)
    {
        $endpoint = $this->endpoint . '/?user_id=1';
        $I->wantTo('Get a list of all organizations that a user belongs to');
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
                'name'    => 'Ushahidi',
                'url'     => 'ushahidi.rollcall.io',
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
            'name' => 'Ushahidi Inc',
            'url' => 'ushahidi.rollcall.io'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Ushahidi Inc',
            'url' => 'ushahidi.rollcall.io',
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
            'name' => 'Rollcall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => '3',
                    'role' => 'admin'
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Rollcall',
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
        $I->wantTo('Transfer org ownership');
        $I->amAuthenticatedAsOrgOwner();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'name' => 'Rollcall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => 3,
                    'role' => 'owner'
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Rollcall',
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
        $I->sendPOST($this->endpoint."/$id/members/add", [
            'members' => [
                [
                    'id'   => 6,
                    'role' => 'member',
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => 6,
                    'role' => 'member',
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
        $I->sendPOST($this->endpoint."/$id/members/add", [
            'members' => [
                [
                    'id'   => 6,
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => 6,
                    'role' => 'member',
                ]
            ]
        ]);
    }

    /*
     * Delete members from an organization as org admin
     *
     */
    public function deleteMembersAsOrgAdmin(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Delete members as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint."/$id/members/delete", [
            'members' => [
                [
                    'id'   => 3,
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'RollCall',
            'url'  => 'rollcall.rollcall.io',
            'members' => [
                [
                    'id'   => 3,
                ]
            ]
        ]);
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
        $I->sendPOST($this->endpoint."/$id/members/add", [
            'members' => [
                [
                    'id'   => 6,
                    'role' => 'admin',
                ]
            ]
        ]);
        $I->seeResponseCodeIs(403);
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
