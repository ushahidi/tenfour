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
                    'subdomain'     => 'rollcall',
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
    public function getOrganizationBySubdomain(ApiTester $I)
    {
        $I->wantTo('Get a list of all organizations by subdomain');
        $I->sendGET($this->endpoint  . '?subdomain=testers');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'organizations' => [[
                'name'    => 'Testers',
                'subdomain'     => 'testers'
            ]]
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
                'subdomain'     => 'rollcall',
                'user' => [
                    'id'   => 1,
                    'role' => 'member',
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
            'subdomain'     => 'rollcall',
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
    // @todo create new endpoint for creating and org and its owner
    // public function createOrganization(ApiTester $I)
    // {
    //     $I->wantTo('Create an organization as user');
    //     $I->amAuthenticatedAsUser();
    //     $I->haveHttpHeader('Content-Type', 'application/json');
    //     $I->sendPOST($this->endpoint, [
    //         'name' => 'Test org',
    //         'subdomain'  => 'test'
    //     ]);
    //     $I->seeResponseCodeIs(200);
    //     $I->seeResponseIsJson();
    //     $I->seeResponseContainsJson([
    //         'name' => 'Test org',
    //         'subdomain' => 'test',
    //         'user' => [
    //             'id'   => 1,
    //             'role' => 'owner',
    //         ]
    //     ]);
    // }

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
            'subdomain'  => 'rollcall',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Rollcall Org',
            'subdomain'  => 'rollcall',
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
            'subdomain'  => 'rollcall',
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
            'subdomain'  => 'rollcall',
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
          'subdomain'  => 'rollcall',
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
            'subdomain'  => 'rollcall',
        ]);
    }

}
