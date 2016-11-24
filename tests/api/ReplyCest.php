<?php

class ReplyCest
{
    protected $endpoint = '/rollcalls';

    public function getReply(ApiTester $I)
    {
        $id = 1;
        $replyId = 1;
        $I->wantTo('Get a reply for a roll call as an organization admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint.'/'.$id.'/reply/'.$replyId);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
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
            ]
        ]);
    }
}
