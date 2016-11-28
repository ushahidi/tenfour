<?php

class RollCallCest
{
    protected $endpoint = '/rollcalls';

    /*
     * Get all roll calls as an admin
     *
     */
    public function getAllRollCalls(ApiTester $I)
    {
        $I->wantTo('Get a list of all roll calls as an admin');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'message' => 'Westgate under siege',
                'organization' => [
                    'id' => 2
                ],
                'sent_count'  => 3,
                'reply_count' => 2,
                'user' => [
                    'id' => 4
                ],
                'recipients' => [
                    [
                        'id' => 1,
                        'name' => 'Test user',
                        'email' => 'test@ushahidi.com',
                        'username' => 'test@ushahidi.com',
                        'created_at' => '-0001-11-30 00:00:00',
                        'updated_at' => '-0001-11-30 00:00:00',
                        'uri' => '/users/1',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Admin user',
                        'email' => 'admin@ushahidi.com',
                        'username' => 'admin@ushahidi.com',
                        'created_at' => '-0001-11-30 00:00:00',
                        'updated_at' => '-0001-11-30 00:00:00',
                        'uri' => '/users/2',
                    ],
                    [
                        'id' => 4,
                        'name' => 'Org owner',
                        'email' => 'org_owner@ushahidi.com',
                        'username' => 'org_owner@ushahidi.com',
                        'created_at' => '-0001-11-30 00:00:00',
                        'updated_at' => '-0001-11-30 00:00:00',
                        'uri' => '/users/4',
                    ]
                ]
            ],
            [
                'message' => 'Another test roll call',
                'organization' => [
                    'id' => 3
                ],
                'sent_count' => 1,
                'user' => [
                    'id' => 1
                ],
                'recipients' => [
                    [
                        'id' => 4,
                        'name' => 'Org owner',
                        'email' => 'org_owner@ushahidi.com',
                        'username' => 'org_owner@ushahidi.com',
                        'created_at' => '-0001-11-30 00:00:00',
                        'updated_at' => '-0001-11-30 00:00:00',
                        'uri' => '/users/4'
                    ]
                ]
            ],
        ]);
    }

    /*
     * Filter roll calls by organization
     *
     */
    public function filterRollCallsbyOrg(ApiTester $I)
    {
        $endpoint = $this->endpoint.'/?organization=2';
        $I->wantTo('Get a list of all roll calls for an organization as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'message' => 'Westgate under siege',
                'organization' => [
                    'id' => 2
                ],
                'sent_count'  => 3,
                'reply_count' => 2,
                'user' => [
                    'id' => 4
                ]
            ]
        ]);
    }

    /*
     * Filter roll calls by user.
     *
     */
    public function filterRollCallsbyUser(ApiTester $I)
    {
        $endpoint = $this->endpoint.'/?organization=2&user=me';
        $I->wantTo('Get a list of all roll calls sent out by a user');
        $I->amAuthenticatedAsOrgOwner();
        $I->sendGET($endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            [
                'message' => 'Westgate under siege',
                'organization' => [
                    'id' => 2
                ],
                'sent_count'  => 3,
                'reply_count' => 2,
                'user' => [
                    'id' => 4
                ]
            ]
        ]);
    }

    /*
     * Get contacts for a roll call
     *
     */
    public function getMessages(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a list of contacts for a roll call as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/messages');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'messages' => [
                [
                    'id'      => 1,
                    'contact' => '0721674180',
                    'type'    => 'phone',
                    'user'    => [
                        'id' => 1,
                    ]
                ],
                [
                    'id'      => 3,
                    'contact' => 'linda@ushahidi.com',
                    'type'    => 'email',
                    'user'    => [
                        'id' => 2,
                    ]
                ],
                [
                    'id'   => 4,
                    'contact' => '0792999999',
                    'type'    => 'phone',
                     'user'    => [
                        'id' => 4,
                    ]
                ]
            ]
        ]);
    }

    /*
     * Get contacts for a roll call
     *
     */
    public function getRecipients(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a list of contacts for a roll call as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/recipients');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'recipients' => [
                [
                    'id' => 1,
                    'name' => 'Test user',
                    'email' => 'test@ushahidi.com',
                    'username' => 'test@ushahidi.com',
                    'created_at' => '-0001-11-30 00:00:00',
                    'updated_at' => '-0001-11-30 00:00:00',
                ],
                [
                    'id' => 2,
                    'name' => 'Admin user',
                    'email' => 'admin@ushahidi.com',
                    'username' => 'admin@ushahidi.com',
                    'created_at' => '-0001-11-30 00:00:00',
                    'updated_at' => '-0001-11-30 00:00:00',
                ],
                [
                    'id' => 4,
                    'name' => 'Org owner',
                    'email' => 'org_owner@ushahidi.com',
                    'username' => 'org_owner@ushahidi.com',
                    'created_at' => '-0001-11-30 00:00:00',
                    'updated_at' => '-0001-11-30 00:00:00',
                ]
            ]
        ]);
    }

    /*
     * Filter contacts who have not responded to a roll call
     *
     */
    public function filterUnresponsiveContacts(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a list of contacts who have not responded to a roll call');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/recipients?unresponsive=true');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'recipients' => [
                [
                    'id' => 2,
                    'name' => 'Admin user',
                    'email' => 'admin@ushahidi.com',
                    'username' => 'admin@ushahidi.com',
                    'created_at' => '-0001-11-30 00:00:00',
                    'updated_at' => '-0001-11-30 00:00:00',
                ],
            ]
        ]);
    }

    /*
     * Get all roll calls in an organization as an authenticated user
     *
     */
    public function getAllRollCallsAsUser(ApiTester $I)
    {
        $I->wantTo('Get a list of all roll calls for an organization as an authenticated user');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    /*
     * Get roll call details as admin
     *
     */
    public function getRollCall(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get roll call details as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Westgate under siege',
                'status'  => 'pending',
                'organization' => [
                    'id' => 2
                ],
                'user' => [
                    'id' => 4
                ]
            ]
         );
    }

    /*
     * Create a roll call as org admin
     *
     */
    public function createRollCall(ApiTester $I)
    {
        $I->wantTo('Create a roll call as admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Westgate under siege, are you ok?',
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 3
                ],
                [
                    'id' => 1
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Westgate under siege, are you ok?',
                'organization' => [
                    'id' => 2
                ],
                'user' => [
                    'id' => 5
                ],
                'recipients' => [
                    [
                        'id' => 3
                    ],
                    [
                        'id' => 1
                    ]
                ]
            ]
        );
    }

    /*
     * Add contact to roll call
     *
     */
    /*public function addContact(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Add contact to roll call');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/contacts', [
            'id'   => 1,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contact' => [
                'id' => 1,
            ]
        ]);
    }*/

    /*
     * Add contacts to roll call
     *
     */
    /*public function addContacts(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Add contact to roll call');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/contacts', [
            [
                'id'   => 1,
            ],
            [
                'id'   => 2,
            ]
        ]
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contacts' => [
                [
                    'id' => 1,
                ],
                [
                    'id' => 2,
                ]
            ]
        ]);
    }*/

    /*
     * Update a rollcall as admin
     *
     */
    public function updateRollCall(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Update roll call details as the admin');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint.'/'.$id, [
            'sent' => 1,
            'status' => 'received',
            'recipients' => [
                [
                    'id' => 1,
                ],
                [
                    'id' => 2
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Westgate under siege',
                'status' => 'received',
                'sent' => 1,
                'organization' => [
                    'id' => 2
                ],
                'recipients' => [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 2
                    ]
                ]
            ]
        );
    }
}
