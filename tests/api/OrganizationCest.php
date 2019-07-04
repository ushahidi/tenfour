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
                    'name'    => 'TenFourTest',
                    'subdomain'     => 'tenfourtest',
                ],
            ]
        ]);
    }

    /*
     * Cannot get a list of organizations without Authentication
     *
     */
    public function cannotGetAllOrganizationsWithoutAuth(ApiTester $I)
    {
        $I->wantTo('Cannot a list of all organizations without auth');
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
            'subscription_status' => 'active'
        ]);
        $I->dontSeeResponseContainsJson([
            'subscriptions' => []
        ]);
        $I->dontSeeResponseContainsJson([
            [
                'name'    => 'Dummy org',
                'subdomain'     => 'dummy'
            ],
            [
                'name'    => 'TenFourTest',
                'subdomain'     => 'tenfourtest'
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
                'name'    => 'TenFourTest',
                'subdomain'     => 'tenfourtest'
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
            'name'      => 'TenFourTest',
            'subdomain' => 'tenfourtest',
            'subscription_status' => 'active',
            'credits'   => 3,
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
            'name'              => 'Test org',
            'subdomain'         => 'test',
            'owner'             => 'Mary Mata',
            'email'             => 'mary@ushahidi.com',
            'password'          => 'testtest',
            'verification_code' => '123456',
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
                'name'          => 'Mary Mata',
                'role'          => 'owner',
                'person_type'   => 'user',
                'contact'       => [
                    'contact'   => 'mary@ushahidi.com',
                    'type'      => 'email',
                    'preferred' => 1
                ]
            ]
        ]);
        $I->cantSeeInDatabase('unverified_addresses', [
            'address' => 'mary@ushahidi.com'
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
            'name' => 'TenFourTest',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'TenFourTest',
        ]);
    }

    /*
     * Update organization subdomain
     *
     * FIXME: Currently, updating a subdomain will fail silently since
     * it's immutable.
     */
    public function updateOrganizationSubdomain(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Update organization details as the admin');
        $I->amAuthenticatedAsOrgOwner();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'name' => 'TenFourTest Org',
            'subdomain'  => 'tenfourtesting',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'TenFourTest Org',
            'subdomain'  => 'tenfourtest',
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
            'name' => 'TenFourTest',
            'subdomain'  => 'tenfourtest',
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
          'name' => 'TenFourTest',
          'subdomain'  => 'testing',
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
            'name' => 'TenFourTest',
            'subdomain'  => 'tenfourtest',
        ]);
    }

    /*
     * Create organization using reserved subdomain
     *
     */
    public function createOrganizationWithReservedDomain(ApiTester $I)
    {
        $I->wantTo('Create an organization using reserved name');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'name'      => 'Application providers',
            'subdomain' => 'app',
            'owner'     => 'Mary Mata',
            'email'     => 'mary@ushahidi.org',
            'password'  => 'testtest',
        ]);
        $I->seeResponseCodeIs(422);
    }


    /*
     * Non-admins can't see restricted settings
     *
     */
    public function viewOrganizationSettingsAsNonAdmin(ApiTester $I)
    {
        $id = 2;

        $I->wantTo('View organization settings as non-admin');
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->amAuthenticatedAsOrgOwner();
        $I->sendPUT($this->endpoint."/$id", [
            'name' => 'TenFourTest',
            'subdomain'  => 'testing',
            'settings'  => ['plan_and_credits' => []],
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendGET($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'settings'      => [
                'key' => 'plan_and_credits'
            ]
        ]);

        $I->amAuthenticatedAsViewer();
        $I->sendGET($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'settings'      => [
                'key' => 'channels'
            ]
        ]);
        $I->dontSeeResponseContainsJson([
            'settings'      => [
                'key' => 'plan_and_credits'
            ]
        ]);

    }

    /*
     * Create organization as new owner
     *
     */
    public function createOrganizationWithInvalidCode(ApiTester $I)
    {
        $I->wantTo('Create an organization with invalid code');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'name'              => 'Test org',
            'subdomain'         => 'test',
            'owner'             => 'Mary Mata',
            'email'             => 'mary@ushahidi.com',
            'password'          => 'testtest',
            'verification_code' => '123457',
        ]);
        $I->seeResponseCodeIs(422);
    }


    /*
     * Lookup an organization
     *
     */
    public function lookupOrganization(ApiTester $I)
    {
        $I->wantTo('Lookup an organization by email address');
        // $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/organization/lookup', [
            'email'             => 'linda@ushahidi.com',
        ]);
        $I->seeResponseCodeIs(200);

        $I->seeRecord('outgoing_mail_log', [
            'subject'     => "Your TenFour domain",
            'type'        => 'orglookup',
            'to'          => 'linda@ushahidi.com',
        ]);
    }

    /*
     * Create organization as new owner
     *
     */
    public function createOrganizationWithTemplates(ApiTester $I)
    {
        $I->wantTo('Create an organization and see zero-state templates');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'name'              => 'Test org',
            'subdomain'         => 'test',
            'owner'             => 'Mary Mata',
            'email'             => 'mary@ushahidi.com',
            'password'          => 'testtest',
            'verification_code' => '123456',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeRecord('check_ins', [
            'message'         => "Are you ok?",
            'template'        => 1,
            'everyone'        => 1,
            'organization_id' => 5,
        ]);
    }
}
