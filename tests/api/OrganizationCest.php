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
        $I->dontSeeResponseContainsJson([
            [
                'name'    => 'Dummy org',
                'subdomain'     => 'dummy'
            ],
            [
                'name'    => 'RollCall',
                'subdomain'     => 'rollcall'
            ]
        ]);
    }

    /*
     * Get all organization by Name
     *
     */
    public function getOrganizationByName(ApiTester $I)
    {
        $I->wantTo('Get a list of all organizations by Name');
        $I->sendGET($this->endpoint  . '?name=Testers');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'organizations' => [
                [
                    'name'    => 'Testers',
                    'subdomain'     => 'testers'
                ]
            ]
        ]);
        $I->dontSeeResponseContainsJson([
            [
                'name'    => 'Dummy org',
                'subdomain'     => 'dummy'
            ],
            [
                'name'    => 'RollCall',
                'subdomain'     => 'rollcall'
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
            'name'      => 'RollCall',
            'subdomain' => 'rollcall',
            'credits'   => 1,
            'user' => [
                'id'   => 4,
                'role' => 'owner',
            ]
        ]);
    }

    /*
     * View another organization by id
     *
     */
    public function viewAnotherOrganizationAsOrgAdmin(ApiTester $I)
    {
        $id = 1;
        $endpoint = $this->endpoint . '/' . $id;
        $I->wantTo('View another organization as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($endpoint);
        $I->seeResponseCodeIs(403);
    }

    /*
     * Create organization as new owner
     *
     */
    public function createOrganization(ApiTester $I)
    {
        $I->wantTo('Create an organization as a new user');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'organization_name' => 'Test org',
            'subdomain'         => 'test',
            'name'              => 'Mary Mata',
            'email'             => 'mary@ushahidi.org',
            'password'          => 'testtest',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name'       => 'Test org',
            'subdomain'  => 'test',
            'settings'   => [
                'key'    => 'channels',
                'values' => [
                    'email' => ['enabled' => true],
                    'sms'   => ['enabled' => true]
                ]
            ],
            'user'      => [
                'name'    => 'Mary Mata',
                'role'    => 'owner',
                'contact' => [
                    'contact' => 'mary@ushahidi.org',
                    'type'    => 'email',
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
          'settings'  => ['channels' => ['email' => ['enabled' => true]]],
      ]);
      $I->seeResponseCodeIs(200);
      $I->seeResponseIsJson();
      $I->seeResponseContainsJson(["organization" => ["settings" => [
        [
          "key" => 'channels',
          "values" => ['email' => ['enabled' => true]]
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
