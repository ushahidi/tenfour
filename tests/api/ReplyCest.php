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
