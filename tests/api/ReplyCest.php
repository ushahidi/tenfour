<?php

class ReplyCest
{
    protected $endpoint = '/api/v1/rollcalls';

    public function getRepliesFilteredByUsers(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a list of replies filtered by user');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/replies?users=4');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'replies' => [
                [
                    'id'       => 3,
                    'message'  => 'Latest answer',
                    'contact'  => [
                        'id' => 4
                    ],
                    'user' => [
                        'id' => 4,
                        'name' => 'Org owner'
                    ]
                ]
            ]
        ]);
    }

    public function getRepliesFilteredByContacts(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a list of replies filtered by contacts');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/replies?contacts=4');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'replies' => [
                [
                    'id'       => 3,
                    'message'  => 'Latest answer',
                    'contact'  => [
                        'id' => 4
                    ],
                    'user' => [
                        'id' => 4,
                        'name' => 'Org owner'
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
            'replies' => [
                [
                    'id'       => 1,
                    'message'  => 'I am OK',
                    'contact'  => [
                        'id'   => 1,
                    ],
                    'user' => [
                        'id' => 1,
                        'name' => 'Test user'
                    ]
                ],
                [
                    'id'       => 3,
                    'message'  => 'Latest answer',
                    'contact'  => [
                        'id' => 4
                    ],
                    'user' => [
                        'id' => 4,
                        'name' => 'Org owner'
                    ]
                ]
            ]
        ]);
    }

    /*
     * Add reply
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
            'answer'   => 'yes'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'reply' => [
                'user' => [
                    'id' => 5
                ],
                'message'  => 'Test response',
                'rollcall' => [
                    'id' => 1,
                ]
            ]
        ]);
    }

    public function getReply(ApiTester $I)
    {
        $id = 1;
        $replyId = 1;
        $I->wantTo('Get a reply for a roll call as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/replies/'.$replyId);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'reply' => [
                'id'       => 1,
                'message'  => 'I am OK',
                'location_text' => NULL,
                'answer' => NULL,
                'uri' => '/rollcalls/1/reply/1',
                'rollcall' => [
                    'id' => 1,
                    'uri' => '/rollcalls/1',
                ],
                'contact'  => [
                    'id'   => 1,
                    'uri' => '/contacts/1',
                  ]
            ]
        ]);
    }

    /*
     * As the user, I want to send a RollCall reply using a reply token
     */
    public function addReplyWithReplyToken(ApiTester $I)
    {
        $roll_call_id = 1;
        $token = 'testtoken1';
        $I->wantTo('Add a reply with a reply token');
        // $I->sendGet();
        $I->sendPOST('/rollcalls/' . $roll_call_id . '/replies', [
            'message'     => 'Test response',
            'answer'      => 'yes',
            'rollCallId'  => $roll_call_id,
            'token'       => $token
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'reply' => [
                'user' => [
                    'id' => 2
                ],
                'message'  => 'Test response',
                'rollcall' => [
                    'id' => 1,
                ]
            ]
        ]);
    }

    /*
     * As another user, I don't want to send a RollCall reply using another user's token
     */
    public function addReplyWithInvalidReplyToken(ApiTester $I)
    {
        $roll_call_id = 1;
        $token = 'testtoken3';
        $I->wantTo('Not add a reply with an invalid reply token');
        // $I->sendGet();
        $I->sendPOST('/rollcalls/' . $roll_call_id . '/replies', [
            'message'     => 'Test response',
            'answer'      => 'yes',
            'rollCallId'  => $roll_call_id,
            'token'       => $token
        ]);
        $I->seeResponseCodeIs(403);
    }

  }
