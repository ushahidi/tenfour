<?php

class RollCallCest
{
    protected $endpoint = '/rollcalls';

    /*
     * Get all roll calls in an organization as an admin
     *
     */
    public function getAllRollCalls(ApiTester $I)
    {
        $I->wantTo('Get a list of all roll calls for an organization as an admin');
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
            ]
        ]);
    }

    /*
     * Get contacts for a roll call
     *
     */
    public function getContacts(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a list of contacts for a roll call as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/contacts');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Westgate under siege',
            'organization' => [
                'id' => 2
            ],
            'contacts' => [
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
     * Filter contacts who have not responded to a roll call
     *
     */
    public function filterUnresponsiveContacts(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a list of contacts who have not responded to a roll call');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/contacts?unresponsive=true');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Westgate under siege',
            'organization' => [
                'id' => 2
            ],
            'contacts' => [
                [
                    'id'      => 3,
                    'contact' => 'linda@ushahidi.com',
                    'type'    => 'email',
                    'user'    => [
                        'id' => 2,
                    ]
                ]
            ]
        ]);
    }

    public function getReplies(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a list of replies for a roll call as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/replies');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Westgate under siege',
            'organization' => [
                'id' => 2
            ],
            'replies' => [
                [
                    'id'       => 1,
                    'message'  => 'I am OK',
                    'contact'  => [
                        'id'   => 1,
                        'user' => [
                            'id' => 1,
                            'name' => 'Test user'
                        ]
                    ]
                ],
                [
                    'id'       => 2,
                    'message'  => 'I am OK',
                    'contact'  => [
                        'id' => 4,
                        'user' => [
                            'id' => 4,
                            'name' => 'Org owner'
                        ]
                    ]
                ]
            ],
            'sent_count'  => 3,
            'reply_count' => 2,
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
            'organization' => 2
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Westgate under siege, are you ok?',
                'organization' => [
                    'id' => 2
                ]
            ]
        );
    }

    /*
     * Add contact to roll call
     *
     */
    public function addContact(ApiTester $I)
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
        $I->seeResponseContainsJson(
            [
                'message' => 'Westgate under siege',
                'organization' => [
                    'id' => 2
                ],
                'contacts' => [
                    [
                        'id' => 1,
                    ]
                ]
            ]
        );
    }

    /*
     * Add reply to roll call
     *
     */
    public function addReply(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Add reply to roll call');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/replies', [
            'message'  => 'Test response',
            'contact'  => 1
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Westgate under siege',
            'organization' => [
                'id' => 2
            ],
            'replies' => [
                [
                    'message' => 'Test response',
                    'contact' => [
                        'id'   => 1,
                    ]
                ]
            ]
        ]);
    }

    /*
     * Add contacts to roll call
     *
     */
    public function addContacts(ApiTester $I)
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
        $I->seeResponseContainsJson(
            [
                'message' => 'Westgate under siege',
                'organization' => [
                    'id' => 2
                ],
                'contacts' => [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 2,
                    ]
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
            'message' => 'Westgate under siege, are you ok?',
            'contact_id' => 1,
            'organization_id' => 1
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            ['message' => 'Westgate under siege, are you ok?',
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
        $I->wantTo('Update roll call details as the admin');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint.'/'.$id, [
            'sent' => 1,
            'status' => 'received'
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
                ]
            ]
        );
    }
}
