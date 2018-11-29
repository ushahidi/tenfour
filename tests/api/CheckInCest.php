<?php

use \TenFour\Models\User;
use DB;

class CheckInCest
{
    //protected $endpoint = '/api/v1/checkins';
    protected $endpoint = '/api/v1/organizations';

    /*
     * Get all check-ins as an admin
     *
     */
    public function getAllCheckIns(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Get a list of all check-ins as an admin');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/checkins');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'checkins' => [
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
                    'message' => 'yet another test check-in',
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
                    'message' => 'check-in with answers',
                ],
                [
                    'id' => 5,
                    'message' => 'check-in with answers',
                ]
            ]
        ]);
    }

    /*
     * Get all check-ins as an admin excluding self tests
     *
     */
    public function getAllCheckInsExcludingSelfTests(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Get a list of all check-ins as an admin excluding self tests');

        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/checkins');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        // include my own self test
        $I->seeResponseContainsJson([
            'checkins' => [
                [
                    'id' => 7,
                    'user' => [
                        'id' => 2
                    ],
                    'self_test_check_in' => 1
                ]
            ]
        ]);

        // exclude others' self tests
        $I->dontSeeResponseContainsJson([
            'checkins' => [
                [
                    'id' => 6,
                    'user' => [
                        'id' => 1
                    ],
                    'self_test_check_in' => 1
                ]
            ]
        ]);
    }

    /*
     * Filter check-ins by organization
     *
     */
    public function filterCheckInsbyOrg(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Get a list of all check-ins for an organization as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/checkins');
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
     * Filter check-ins by user.
     *
     */
    public function filterCheckInsbyUser(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Get a list of all check-ins sent out by a user');
        $I->amAuthenticatedAsOrgOwner();
        $I->sendGET($this->endpoint.'/'.$id.'&user=me/checkins');
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
     * Get contacts for a check-in
     *
     */
    public function getMessages(ApiTester $I)
    {
        $id = 1;
        $check_in_id = 1;
        $I->wantTo('Get a list of contacts for a check-in as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/checkins/'.$check_in_id.'/messages');
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
                    'id'   => 10,
                    'contact' => 'test+contact2@ushahidi.com',
                    'type'    => 'email',
                     'user'    => [
                        'id' => 1,
                    ]
                ]
            ]
        ]);
    }

    /*
     * Get contacts for a check-in
     *
     */
    public function getRecipients(ApiTester $I)
    {
        $id = 1;
        $check_in_id = 1;
        $I->wantTo('Get a list of contacts for a check-in as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        //$I->sendGET($this->endpoint.'/'.$id.'/recipients');
        $I->sendGET($this->endpoint.'/'.$id.'/checkins/'.$check_in_id.'/recipients');
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
     * Get all check-ins in an organization as an authenticated user
     *
     */
    public function getAllCheckInsAsUser(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a list of all check-ins for an organization as an authenticated user');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint.'/'.$id.'/checkins');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        // user is a recipient
        $I->seeResponseContainsJson([
            'checkins' => [
                [
                    'id' => 2,
                    'message' => 'Another test check-in',
                    'organization' => [
                         'id' => 3
                    ],
                    'sent_count'  => 2,
                    'reply_count' => 0,
                    'user' => [
                         'id' => 1
                    ],
                    'recipients' => [
                        [
                            'id' => 4,
                            'name' => 'Org owner',
                            'uri' => '/users/4',
                        ],
                        [
                            'id' => 3,
                            'name' => 'Org member',
                            'uri' => '/users/3',
                        ]
                   ]
                ]
            ]
        ]);

        // organization is different
        $I->dontSeeResponseContainsJson([
            [
                'id' => 2,
                'message' => 'Another test check-in',
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
                'message' => 'yet another test check-in',
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
                'self_test_check_in' => 1
            ]
        ]);
    }

    /*
     * Get check-in details as admin
     *
     */
    public function getCheckIn(ApiTester $I)
    {
        $id = 1;
        $check_in_id = 1;
        $I->wantTo('Get check-in details as an org admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/checkins/'.$check_in_id);
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
     * Get check-in details as admin
     *
     */
    public function getOtherCheckInAsMember(ApiTester $I)
    {
        $id = 2;
        $check_in_id = 5;
        $I->wantTo('Failed to get check-in details as an member');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint.'/'.$id.'/checkins/'.$check_in_id);
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    /*
     * Create a check-in as org admin
     *
     */
    public function createCheckIn(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create a check-in as admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
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
        // This recipient DID respond to previous check-in
        $I->seeRecord('check_in_recipients', [
            'user_id'         => 1,
            'check_in_id'    => 1,
            'response_status' => 'replied',
        ]);
        // This recipient did not respond to previous check-in
        $I->seeRecord('check_in_recipients', [
            'user_id'         => 4,
            'check_in_id'    => 1,
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
     * Send check-in to self
     *
     */
    public function sendCheckInToSelf(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Send check-in to self');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
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
     * Create a check-in as org admin
     *
     */
    public function createCheckInWithoutAnswers(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create a check-in as admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins/', [
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
     * Create a check-in with credits
     *
     */
    public function createCheckInWithCredits(ApiTester $I)
    {
        $id = 2;
        $credits_before = 3;
        $credits_after = 1;

        $I->wantTo('Create a check-in with credits');
        $I->amAuthenticatedAsOrgAdmin();

        $I->haveHttpHeader('Content-Type', 'application/json');
        //$I->sendPUT('/api/v1/organizations/2', [
        $I->sendPUT($this->endpoint.'/'.$id, [
            'name' => 'TenFourTest Org',
            'subdomain'  => 'tenfourtest',
            'settings'  => ['channels' => ['sms' => ['enabled' => true]]],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'credits'   => $credits_before,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
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

        //$I->sendGET('/api/v1/organizations/2');
        $I->sendGET($this->endpoint.'/'.$id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([ 'organization' => [
            'id'        => 2,
            'credits'   => $credits_after,
        ]]);
    }

    /*
     * Create a check-in without enough credits
     *
     */
    public function createCheckInWithoutCredits(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create a check-in without enough credits');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
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
     * Create an app only check-in without enough credits
     *
     */
    public function createAppOnlyCheckInWithoutCredits(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create an "app only" check-in without enough credits');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Westgate under siege, are you ok?',
            'organization_id' => 2,
            'send_via' => ['app'],
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
     * Create a check-in with errors
     *
     */
    public function createCheckInWithErrors(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create an invalid check-in as admin and get errors');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
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
     * Add contact to check-in
     *
     */
    /*public function addContact(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Add contact to check-in');
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
     * Add contacts to check-in
     *
     */
    /*public function addContacts(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Add contact to check-in');
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
     * Update a check-in as admin
     *
     */
    public function updateCheckIn(ApiTester $I)
    {
        $id = 2;
        $check_in_id = 1;
        $I->wantTo('Update check-in details as the admin');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint.'/'.$id.'/checkins/'.$check_in_id, [
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
     * Send check-in to single recipient
     *
     */
    public function sendCheckInToRecipient(ApiTester $I)
    {
        $id = 1;
        $check_in_id = 1;
        $recipient_id = 4;
        $I->wantTo('Send check-in to a single recipient');
        $I->seeRecord('check_in_recipients', [
            'user_id'         => 4,
            'check_in_id'    => 1,
            'response_status' => 'unresponsive',
        ]);
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins/'.$check_in_id.'/recipients/' .$recipient_id. '/messages');
        //$I->sendPOST($this->endpoint.'/'.$id.'/recipients/' .$recipient_id. '/messages');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'recipient'=> [
                    'id' => 4,
                    'response_status' => 'waiting'
                ]
            ]
        );
    }

    /*
     * Delete check-in
     */
    public function deleteCheckIn(ApiTester $I)
    {
        $id = 2;
        $check_in_id = 1;
        $I->wantTo('Delete a check-in');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint.'/'.$id.'/checkins/'.$check_in_id);
        $I->seeResponseCodeIs(405);
    }

    /*
     * As the user, I want to get a check-in using a reply token
     */
    public function getCheckInWithReplyToken(ApiTester $I)
    {
        $check_in_id = 1;
        $token = 'testtoken1';
        $I->wantTo('Get a check-in using a reply token');
        $I->sendGet('/checkins/' . $check_in_id . '?token=' . $token);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([ 'checkin' =>
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
         ]);
    }

    /*
     * As another user, I don't want to get a CheckIn using invalid reply token
     */
    public function getCheckInWithInvalidReplyToken(ApiTester $I)
    {
        $check_in_id = 1;
        $token = 'testtoken3';
        $I->wantTo('Not get a check-in using an invalid reply token');
        $I->sendGet('/checkins/' . $check_in_id . '?token=' . $token);
        $I->seeResponseCodeIs(403);
    }

    /*
     * Send check-ins with rotating numbers
    */
    public function sendCheckInsWithRotatingNumbers(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Send check-ins with rotating numbers');
        $I->amAuthenticatedAsOrgAdmin();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Alien Attack! are you ok?',
            'send_via' => ['sms'],
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => [
              ['answer'=>'No'],
              ['answer'=>'Yes']
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Alien Attack Part II! are you ok?',
            'send_via' => ['sms'],
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => [
              ['answer'=>'No'],
              ['answer'=>'Yes']
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        // All SMS has been sent
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20880',
            'check_in_id' => '10',
            'type'        => 'check_in',
            'message'     => "Org admin from TenFourTest says: Alien Attack! are you ok?\nReply with \"No\" or \"Yes\" in your response"
        ]);
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20880',
            'check_in_id' => '10',
            'type'        => 'check_in_url',
        ]);
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20881',
            'check_in_id' => '11',
            'type'        => 'check_in',
            'message'     => "Org admin from TenFourTest says: Alien Attack Part II! are you ok?\nReply with \"No\" or \"Yes\" in your response"
        ]);
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20881',
            'check_in_id' => '11',
            'type'        => 'check_in_url',
        ]);
    }

    /*
     * Send check-in reminder to a contact when all outgoing numbers
     * are active.
     */
    public function receiveACheckInReminder(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Receive a check-in reminder');
        $I->amAuthenticatedAsOrgAdmin();

        $attack = [
            'send_via' => ['sms'],
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => [
              ['answer'=>'No'],
              ['answer'=>'Yes']
            ]
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $attack['message'] = 'Alien Attack Part 1!';
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', $attack);
        $I->seeResponseCodeIs(200);
        $attack['message'] = 'Alien Attack Part 2!';
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', $attack);
        $I->seeResponseCodeIs(200);
        $attack['message'] = 'Alien Attack Part 3!';
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', $attack);
        $I->seeResponseCodeIs(200);

        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20880',
            'check_in_id' => '10',
            'type'        => 'reminder'
        ]);
        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20880',
            'check_in_id' => '12',
            'type'        => 'check_in',
            'message'     => "Org admin from TenFourTest says: Alien Attack Part 3!\nReply with \"No\" or \"Yes\" in your response"
        ]);
    }

    /*
     * Resend a check-in should resend to unreplied users
     *
     */
    public function resendCheckInToUnreplied(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Resend a check-in to unreplied');
        $I->amAuthenticatedAsOrgAdmin();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Resending a check-in',
            'organization_id' => 2,
            'send_via' => ['sms'],
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => [
              ['answer'=>'No'],
              ['answer'=>'Yes']
            ]
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendPUT('/api/v1/organizations/2/people/1/contacts/1', [
            'contact'         => '+254721674181',
            'type'            => 'phone',
            'organization_id' => 2,
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendPUT($this->endpoint.'/'.$id.'/checkins/10', [
            'message' => 'Resending a check-in',
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
            'answers' => [
              ['answer'=>'No'],
              ['answer'=>'Yes']
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['checkin' =>
            [
                'message' => 'Resending a check-in',
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

        $I->seeRecord('check_in_recipients', [
            'user_id'         => 1,
            'check_in_id'    => 10,
            'response_status' => 'waiting',
        ]);

        $I->seeRecord('check_in_recipients', [
            'user_id'         => 10,
            'check_in_id'    => 10,
            'response_status' => 'waiting',
        ]);

        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674181',
            'from'        => '20880',
            'check_in_id' => '10',
            'type'        => 'check_in'
        ]);

        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674181',
            'from'        => '20880',
            'check_in_id' => '10',
            'type'        => 'check_in'
        ]);

        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254722123457',
            'from'        => '20880',
            'check_in_id' => '10',
            'type'        => 'check_in'
        ]);

        $I->seeNumRecords(0, 'outgoing_sms_log', [
            'check_in_id' => '10',
            'type'        => 'reminder'
        ]); // from a previous test
    }

    /*
     * Resend a check-in should not resend to replied users
     *
     */
    public function resendCheckInNotToReplied(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Resend a check-in to replied');
        $I->amAuthenticatedAsOrgAdmin();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Resending a check-in',
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

        $I->sendPOST($this->endpoint.'/'.$id.'/checkins/10/replies', [
            'message'  => 'Test response',
            'answer'   => 'yes'
        ]);
        $I->seeResponseCodeIs(200);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint.'/'.$id.'/checkins/10', [
            'message' => 'Resending a check-in',
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

        $I->sendGET($this->endpoint.'/'. $id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['organization' => [
            'name'      => 'TenFourTest',
            'subdomain' => 'tenfourtest',
            'subscription_status' => 'active',
            'credits'   => 1,       // <-- I should only have used 2 credits
            'user' => [
                'id'   => 4,
                'role' => 'owner',
            ]
        ]]);
    }


    /*
     * Create a check-in as author
     *
     */
    public function createCheckInAsAuthor(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create a check-in as author');
        $I->amAuthenticatedAsAuthor();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Westgate under siege!',
            'organization_id' => 2,
            'send_via' => ['app'],
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);
    }

    /*
     * Don't send reminder when no response asked for
     */
    public function dontReceiveACheckInReminder(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Not receive a check-in reminder when no response was asked for');
        $I->amAuthenticatedAsOrgAdmin();

        $attack = [
            'message' => 'Message incoming...',
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
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', $attack);
        $I->seeResponseCodeIs(200);

        $attack = [
            'message' => 'We are under attack. Stay tuned for next message.',
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
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', $attack);
        $I->seeResponseCodeIs(200);

        $attack = [
            'message' => 'Are you ok?',
            'send_via' => ['sms'],
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 1
                ]
            ],
            'answers' => [
              ['answer'=>'No'],
              ['answer'=>'Yes']
            ]
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', $attack);
        $I->seeResponseCodeIs(200);


        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20881',
            'check_in_id' => '11',
            'type'        => 'check_in',
            'message'     => "Org admin from TenFourTest says: We are under attack. Stay tuned for next message.\n"
        ]);

        $I->dontSeeRecord('outgoing_sms_log', [
            'to'          => '+254721674180',
            'from'        => '20881',
            'check_in_id' => '11',
            'type'        => 'reminder'
        ]);
    }


    /*
     * Don't allow duplicate answers #1096
     *
     */
    public function createCheckInWithDuplicateAnswers(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create check-in with duplicate answers');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'check-in with duplicate answers',
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 3
                ]
            ],
            'answers' => [
              ['answer'=>'Yes','color'=>'#BC6969','icon'=>'icon-exclaim','type'=>'negative'],
              ['answer'=>'Yes','color'=>'#E8C440','icon'=>'icon-check','type'=>'positive']
            ]
        ]);
        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                "message" => "422 Unprocessable Entity",
                "errors" =>  ["answers.0.answer"=>["validation.distinct"],"answers.1.answer"=>["validation.distinct"]],
                "status_code" => 422
            ]
        );
    }

    /*
     * Send one-way check-ins
    */
    public function sendOneWayCheckIn(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Send a check-in to a region that only supports one-way SMS');
        $I->amAuthenticatedAsOrgAdmin();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Alien Attack! are you ok?',
            'send_via' => ['sms'],
            'organization_id' => 2,
            'recipients' => [
                [
                    'id' => 13
                ]
            ],
            'answers' => [
              ['answer'=>'No'],
              ['answer'=>'Yes']
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeRecord('outgoing_sms_log', [
            'to'          => '+964721674200',
            'from'        => 'TenFour',
            'check_in_id' => '10',
            'type'        => 'check_in',
            'message'     => "Org admin from TenFourTest says: Alien Attack! are you ok?\n"
        ]);
    }

    /*
     * Create a group check-in
     *
     */
    public function createGroupCheckIn(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create a group check-in');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Are you a group?',
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
            'group_ids' => [1],
            'answers' => []
        ]);
        $I->seeRecord('check_in_groups', [
            'group_id'       => 1,
            'check_in_id'    => 9,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'groups' => [
                    [ 'id' => 1 ]
                ]
            ]
        );
    }

    /*
     * Create an "everyone" check-in
     *
     */
    public function createEveryoneCheckIn(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create an everyone check-in');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Are you everyone?',
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
            'group_ids' => [],
            'everyone' => true,
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([ 'checkin' =>
            [
                'everyone' => 1
            ]
        ]);
    }

    /*
     * Create a check-in template
     *
     */
    public function createCheckInTemplate(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Create a check-in template');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint.'/'.$id.'/checkins', [
            'message' => 'Template',
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
            'group_ids' => [],
            'template' => true,
            'answers' => []
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([ 'checkin' =>
            [
                'template' => 1
            ]
        ]);
    }

    /*
     * Get organization's check-in templates
     *
     */
    public function getOrganizationCheckInTemplates(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Get a list of all check-ins template for an organization');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/checkins?template=true');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([ 'checkins' =>
            [
                'id' => 8,
                'message' => 'check-in template',
                'template' => 1
            ]
        ]);
        $I->dontSeeResponseContainsJson([ 'checkins' =>
            [
                'id' => 1,
                'message' => 'Westgate under siege'
            ]
        ]);
    }

    /*
     * Delete a check-in template
     *
     */
    public function deleteCheckInTemplate(ApiTester $I)
    {
        $id = 2;
        $I->wantTo('Delete a check-in template');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint.'/'.$id.'/checkins/8', [
            'template' => false,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([ 'checkin' =>
            [
                'template' => 0
            ]
        ]);
    }
}
