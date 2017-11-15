<?php

class RollCallCest
{
    protected $endpoint = '/api/v1/rollcalls';

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
            'rollcalls' => [
                [
                    'id' => 1,
                    'message' => 'Westgate under siege',
                    'organization' => [
                        'id' => 2
                    ],
                    'sent_count'  => 4,
                    'reply_count' => 2,
                    'user' => [
                        'id' => 4
                    ],
                    'recipients' => [
                        [
                            'id' => 1,
                            'name' => 'Test user',
                            'uri' => '/users/1',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Admin user',
                            'uri' => '/users/2',
                        ],
                        [
                            'id' => 4,
                            'name' => 'Org owner',
                            'uri' => '/users/4',
                        ]
                    ],
                    'replies' => [
                        [
                            'id' => 1,
                            'message' => 'I am OK'
                        ],
                        [
                            'id' => 3,
                            'message' => 'Latest answer'
                        ]
                    ]
                ],
                [
                    'message' => 'yet another test roll call',
                    'organization' => [
                        'id' => 2
                    ],
                    'sent_count' => 0,
                    'user' => [
                        'id' => 1
                    ],
                    'recipients' => [
                        [
                            'id' => 3
                        ]
                    ],
                    'replies' => [
                        [
                            'message' => 'Latest answer again',
                            'id' => 5
                        ]
                    ],
                ],
                [
                    'id' => 4,
                    'message' => 'Roll call with answers',
                ],
                [
                    'id' => 5,
                    'message' => 'Roll call with answers',
                ]
            ]
        ]);
    }

    /*
     * Get all roll calls as an admin excluding self tests
     *
     */
    public function getAllRollCallsExcludingSelfTests(ApiTester $I)
    {
        $I->wantTo('Get a list of all roll calls as an admin excluding self tests');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        // include my own self test
        $I->seeResponseContainsJson([
            'rollcalls' => [
                [
                    'id' => 7,
                    'user' => [
                        'id' => 2
                    ],
                    'self_test_roll_call' => 1
                ]
            ]
        ]);

        // exclude others' self tests
        $I->dontSeeResponseContainsJson([
            'rollcalls' => [
                [
                    'id' => 6,
                    'user' => [
                        'id' => 1
                    ],
                    'self_test_roll_call' => 1
                ]
            ]
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
                'sent_count'  => 4,
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
                'sent_count'  => 4,
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
                    'contact' => '+254721674180',
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
                    'contact' => '+254792999999',
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
                ],
                [
                    'id' => 2,
                    'name' => 'Admin user',
                ],
                [
                    'id' => 4,
                    'name' => 'Org owner',
                ]
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
        $I->sendGET($this->endpoint.'?organization=2');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        // user is a recipient
        $I->seeResponseContainsJson([
            [
                'id' => 1,
                'message' => 'Westgate under siege',
                'organization' => [
                    'id' => 2
                ],
                'sent_count'  => 4,
                'reply_count' => 2,
                'user' => [
                    'id' => 4
                ],
                'recipients' => [
                    [
                        'id' => 1,
                        'name' => 'Test user',
                        'uri' => '/users/1',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Admin user',
                        'uri' => '/users/2',
                    ],
                    [
                        'id' => 4,
                        'name' => 'Org owner',
                        'uri' => '/users/4',
                    ]
                ]
            ]
        ]);

        // organization is different
        $I->dontSeeResponseContainsJson([
            [
                'id' => 2,
                'message' => 'Another test roll call',
                'organization' => [
                    'id' => 3
                ],
                'sent_count' => 1,
                'user' => [
                    'id' => 1
                ],
                'recipients' => []
            ],
        ]);

        // user is sender
        $I->seeResponseContainsJson([
            [
                'id' => 3,
                'message' => 'yet another test roll call',
                'user' => [
                    'id' => 1
                ],
            ]
        ]);

        // user's self test
        $I->seeResponseContainsJson([
            [
                'id' => 6,
                'user' => [
                    'id' => 1
                ],
                'self_test_roll_call' => 1
            ]
        ]);
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
     * Get roll call details as admin
     *
     */
    public function getOtherRollCallAsMember(ApiTester $I)
    {
        $id = 5;
        $I->wantTo('Failed to get roll call details as an member');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint.'/'.$id);
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
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
            'send_via' => ['email'],
            'recipients' => [
                [
                    'id' => 3
                ],
                [
                    'id' => 1
                ]
            ],
            'answers' => [
              ['answer'=>'No','color'=>'#BC6969','icon'=>'icon-exclaim','type'=>'negative'],
              ['answer'=>'Yes','color'=>'#E8C440','icon'=>'icon-check','type'=>'positive']
            ]
        ]);
        // This recipient DID respond to previous roll call
        $I->seeRecord('roll_call_recipients', [
            'user_id'         => 1,
            'roll_call_id'    => 1,
            'response_status' => 'replied',
        ]);
        // This recipient did not respond to previous roll call
        $I->seeRecord('roll_call_recipients', [
            'user_id'         => 3,
            'roll_call_id'    => 2,
            'response_status' => 'unresponsive',
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
     * Send roll call to self
     *
     */
    public function sendRollCallToSelf(ApiTester $I)
    {
        $I->wantTo('Send roll call to self');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Test message to self',
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 1
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Test message to self',
                'organization' => [
                    'id' => 2
                ],
                'user' => [
                    'id' => 1
                ],
                'recipients' => [
                    [
                        'id' => 1
                    ]
                ]
            ]
        );
    }

    /*
     * Create a roll call as org admin
     *
     */
    public function createRollCallWithoutAnswers(ApiTester $I)
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
            ],
            'answers' => []
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
     * Create a roll call with credits
     *
     */
    public function createRollCallWithCredits(ApiTester $I)
    {
        $credits_before = 3;
        $credits_after = 1;

        $I->wantTo('Create a roll call with credits');
        $I->amAuthenticatedAsOrgAdmin();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/api/v1/organizations/2', [
            'name' => 'Rollcall Org',
            'subdomain'  => 'testing',
            'settings'  => ['channels' => ['sms' => ['enabled' => true]]],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'credits'   => $credits_before,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Westgate under siege, are you ok?',
            'organization_id' => 2,
            'send_via' => ['sms'],
            'recipients' => [
                [
                    'id' => 9
                ],
                [
                    'id' => 4
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->sendGET('/api/v1/organizations/2');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([ 'organization' => [
            'id'        => 2,
            'credits'   => $credits_after,
        ]]);
    }

    /*
     * Create a roll call without enough credits
     *
     */
    public function createRollCallWithoutCredits(ApiTester $I)
    {
        $I->wantTo('Create a rollcall without enough credits');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Westgate under siege, are you ok?',
            'organization_id' => 2,
            'send_via' => ['preferred', 'sms'],
            'recipients' => [
                [
                    'id' => 1
                ],
                [
                    'id' => 4
                ],
                [
                    'id' => 9
                ],
                [
                    'id' => 10
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(402);
    }

    /*
     * Create an app only roll call without enough credits
     *
     */
    public function createAppOnlyRollCallWithoutCredits(ApiTester $I)
    {
        $I->wantTo('Create an "app only" rollcall without enough credits');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Westgate under siege, are you ok?',
            'organization_id' => 2,
            'send_via' => ['apponly'],
            'recipients' => [
                [
                    'id' => 1
                ],
                [
                    'id' => 4
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);
    }

    /*
     * Create a roll call with errors
     *
     */
    public function createRollCallWithErrors(ApiTester $I)
    {
        $I->wantTo('Create an invalid roll call as admin and get errors');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => '',
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 3
                ],
                [
                    'id' => 9999
                ]
            ]
        ]);
        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                "message" => "422 Unprocessable Entity",
                "errors" =>  [
                    "message" =>  ["The message field is required."],
                    "recipients.1.id" => ["The selected recipients.1.id is invalid."]
                ],
                "status_code" => 422
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
            'organization_id' => 2,
            'status' => 'received',
            'recipients' => [
                [
                    'id' => 1,
                ],
                [
                    'id' => 2
                ],
                [
                    'id' => 3
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
                    ],
                    [
                        'id' => 3
                    ]
                ]
            ]
        );
    }

    /*
     * Send roll call to single recipient
     *
     */
    public function sendRollCallToRecipient(ApiTester $I)
    {
        $id = 1;
        $recipient_id = 4;
        $I->wantTo('Send roll call to a single recipient');
        $I->seeRecord('roll_call_recipients', [
            'user_id'         => 4,
            'roll_call_id'    => 1,
            'response_status' => 'unresponsive',
        ]);
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/recipients/' .$recipient_id. '/messages');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'id' => 4,
                'response_status' => 'waiting'
            ]
        );
    }

    /*
     * Delete roll call
     */
    public function deleteRollCall(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Delete a roll call');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id");
        $I->seeResponseCodeIs(405);
    }

    /*
     * As the user, I want to get a RollCall using a reply token
     */
    public function getRollCallWithReplyToken(ApiTester $I)
    {
        $roll_call_id = 1;
        $token = 'testtoken1';
        $I->wantTo('Get a RollCall using a reply token');
        $I->sendGet('/rollcalls/' . $roll_call_id . '?token=' . $token);
        $I->seeResponseCodeIs(200);
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
     * As another user, I don't want to get a RollCall using invalid reply token
     */
    public function getRollCallWithInvalidReplyToken(ApiTester $I)
    {
        $roll_call_id = 1;
        $token = 'testtoken3';
        $I->wantTo('Not get a RollCall using an invalid reply token');
        $I->sendGet('/rollcalls/' . $roll_call_id . '?token=' . $token);
        $I->seeResponseCodeIs(403);
    }

    /*
     * Send RollCalls with rotating numbers
    */
    public function sendRollCallsWithRotatingNumbers(ApiTester $I)
    {
        $I->wantTo('Send RollCalls with rotating numbers');
        $I->amAuthenticatedAsOrgAdmin();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Alien Attack! are you ok?',
            'send_via' => ['sms'],
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Alien Attack Part II! are you ok?',
            'send_via' => ['sms'],
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        // All SMS has been sent
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20880',
            'rollcall_id' => '8',
            'type'        => 'rollcall',
            'message'     => "Alien Attack! are you ok?\nReply with \"OK\" in your response"
        ]);
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20880',
            'rollcall_id' => '8',
            'type'        => 'rollcall_url',
        ]);
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20881',
            'rollcall_id' => '9',
            'type'        => 'rollcall',
            'message'     => "Alien Attack Part II! are you ok?\nReply with \"OK\" in your response"
        ]);
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20881',
            'rollcall_id' => '9',
            'type'        => 'rollcall_url',
        ]);
    }

    /*
     * Send RollCall reminder to a contact when all outgoing numbers
     * are active.
     */
    public function receiveARollCallReminder(ApiTester $I)
    {
        $I->wantTo('Receive a RollCall reminder');
        $I->amAuthenticatedAsOrgAdmin();

        $attack = [
            'send_via' => ['sms'],
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => []
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $attack['message'] = 'Alien Attack Part 1!';
        $I->sendPOST($this->endpoint, $attack);
        $I->seeResponseCodeIs(200);
        $attack['message'] = 'Alien Attack Part 2!';
        $I->sendPOST($this->endpoint, $attack);
        $I->seeResponseCodeIs(200);
        $attack['message'] = 'Alien Attack Part 3!';
        $I->sendPOST($this->endpoint, $attack);
        $I->seeResponseCodeIs(200);

        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20880',
            'rollcall_id' => '8',
            'type'        => 'reminder'
        ]);
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20880',
            'rollcall_id' => '10',
            'type'        => 'rollcall',
            'message'     => "Alien Attack Part 3!\nReply with \"OK\" in your response"
        ]);
    }

    /*
     * Resend a RollCall should resend to unreplied users
     *
     */
    public function resendRollCallToUnreplied(ApiTester $I)
    {
        $I->wantTo('Resend a RollCall to unreplied');
        $I->amAuthenticatedAsOrgAdmin();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Resending a RollCall',
            'organization_id' => 2,
            'send_via' => ['sms'],
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendPUT('/api/v1/organizations/2/people/1/contacts/1', [
            'contact'         => '+254721674181',
            'type'            => 'phone',
            'organization_id' => 2,
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendPUT($this->endpoint.'/8', [
            'message' => 'Resending a RollCall',
            'organization_id' => 2,
            'send_via' => ['sms'],
            'recipients' => [
                [
                    'id' => 10
                ],
                [
                    'id' => 1
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['rollcall' =>
            [
                'message' => 'Resending a RollCall',
                'organization' => [
                    'id' => 2
                ],
                'user' => [
                    'id' => 5
                ],
                'recipients' => [
                    [
                        'id' => 10
                    ],
                    [
                        'id' => 1
                    ]
                ]
            ]
        ]);

        $I->seeRecord('roll_call_recipients', [
            'user_id'         => 1,
            'roll_call_id'    => 8,
            'response_status' => 'waiting',
        ]);

        $I->seeRecord('roll_call_recipients', [
            'user_id'         => 10,
            'roll_call_id'    => 8,
            'response_status' => 'waiting',
        ]);

        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20880',
            'rollcall_id' => '8',
            'type'        => 'rollcall'
        ]);

        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674181',
            'from'        => '20880',
            'rollcall_id' => '8',
            'type'        => 'rollcall'
        ]);

        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254722123457',
            'from'        => '20880',
            'rollcall_id' => '8',
            'type'        => 'rollcall'
        ]);

        $I->seeNumRecords(1, 'outgoing_sms_log', [
            'rollcall_id' => '8',
            'type'        => 'reminder'
        ]); // from a previous test
    }

    /*
     * Resend a RollCall should not resend to replied users
     *
     */
    public function resendRollCallNotToReplied(ApiTester $I)
    {
        $I->wantTo('Resend a RollCall to replied');
        $I->amAuthenticatedAsOrgAdmin();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'message' => 'Resending a RollCall',
            'organization_id' => 2,
            'send_via' => ['sms'],
            'recipients' => [
                [
                    'id' => 5
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendPOST($this->endpoint.'/8/replies', [
            'message'  => 'Test response',
            'answer'   => 'yes'
        ]);
        $I->seeResponseCodeIs(200);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint.'/8', [
            'message' => 'Resending a RollCall',
            'organization_id' => 2,
            'send_via' => ['sms'],
            'recipients' => [
                [
                    'id' => 5
                ],
                [
                    'id' => 4
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendGET('/api/v1/organizations'."/2");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['organization' => [
            'name'      => 'RollCall',
            'subdomain' => 'rollcall',
            'subscription_status' => 'active',
            'credits'   => 1,       // <-- I should only have used 2 credits
            'user' => [
                'id'   => 4,
                'role' => 'owner',
            ]
        ]]);
    }
}
