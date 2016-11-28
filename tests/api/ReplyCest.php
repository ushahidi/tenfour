<?php

class ReplyCest
{
    protected $endpoint = '/rollcalls';

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
                    'id'       => 2,
                    'message'  => 'I am OK',
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
                    'id'       => 2,
                    'message'  => 'I am OK',
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
                    'id'       => 2,
                    'message'  => 'I am OK',
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
            'message'  => 'Test response'
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
                'created_at' => '-0001-11-30 00:00:00',
                'updated_at' => '-0001-11-30 00:00:00',
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
}
