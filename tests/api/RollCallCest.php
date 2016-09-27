<?php

class RollCallCest
{
    protected $endpoint = '/rollcalls';

    /*
     * Get all rollcalls in an organization as an admin
     *
     */
    public function getAllRollCalls(ApiTester $I)
    {
        $I->wantTo('Get a list of all rollcalls for an organization as an admin');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'message' => 'Westgate under seige',
                'organization' => [
                    'id' => 2
                ],
                'contact' => [
                    'id' => 4
                ]
            ]
        ]);
    }

    /*
     * Filter rollcalls by organization
     *
     */
    public function filterRollCallsbyOrg(ApiTester $I)
    {
        $endpoint = $this->endpoint . '/?org_id=2';
        $I->wantTo('Get a list of all rollcalls for an organization as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'message' => 'Westgate under seige',
                'organization' => [
                    'id' => 2
                ],
                'contact' => [
                    'id' => 4
                ]
            ]
        ]);

    }

    /*
     * Get all rollcalls in an organization as an authenticated user
     *
     */
    public function getAllRollCallsAsUser(ApiTester $I)
    {
        $I->wantTo('Get a list of all rollcalls for an organization as an authenticated user');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    /*
     * Get rollcall details as admin
     *
     */
    public function getRollCall(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get rollcall details as an admin');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Westgate under seige',
                'organization' => [
                    'id' => 2
                ],
                'contact' => [
                    'id' => 4
                ]
            ]
         );
    }

    /*
     * Create a rollcall as admin
     *
     */
    public function createRollCall(ApiTester $I)
    {
        $I->wantTo('Create a rollcall as admin');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Westgate under seige, are you ok?',
            'contact_id' => 1,
            'organization_id' => 1
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Westgate under seige, are you ok?',
                'organization' => [
                    'id' => 1
                ],
                'contact' => [
                    'id' => 1
                ]
            ]
        );
    }

    /*
     * Create a rollcall as a registered member
     *
     *//*
    public function createRollCallAsMember(ApiTester $I)
    {
        $I->wantTo('Create a rollcall as a registered member');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Westgate under seige, are you ok?',
            'contact_id' => 1,
            'organization_id' => 1
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            ['message' => 'Westgate under seige, are you ok?',
             'organization' => ['id' => 1],
             'contact' => ['id' => 1 ]
            ]
        );
    }

    /*
     * Update a rollcall as admin
     *
     */
    public function updateRollCall(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Update rollcall details as the admin');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'message' => 'Westgate has been cordoned',
            'contact_id' => 1,
            'organization_id' => 1
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Westgate has been cordoned',
                'contact' => [
                    'id' => 1
                ],
                'organization' => [
                    'id' => 1
                ]
            ]
        );
    }
}
