<?php

/* Test MOs pushed by the provider to webhooks */

class SMSCest
{
    public function receiveAfricasTalkingMOs(ApiTester $I)
    {
        $endpoint = 'sms/receive/africastalking';
        $I->wantTo('Receive MOs from Africa\'s Talking');
        $I->sendPost($endpoint, [
            'from' => '0721674180',
            'text' => 'test MO from Africa\'s talking',
            'id' => '111111',
        ]);

        $I->seeRecord('replies', [
            'message' => 'test MO from Africa\'s talking',
            'message_id' => '111111',
        ]);
        
        $I->seeResponseCodeIs(200);
    }

    public function receiveNexmoMOs(ApiTester $I)
    {
        $endpoint = 'sms/receive/nexmo';
        $I->wantTo('Receive MOs from Nexmo');
        $I->sendPost($endpoint, [
            'msisdn' => '0721674180',
            'text' => 'test MO from Nexmo',
            'messageId' => '000000FFFB0356D1',
        ]);

        $I->seeRecord('replies', [
            'message' => 'test MO from Nexmo',
            'message_id' => '000000FFFB0356D1',
        ]);
        
        $I->seeResponseCodeIs(200);
    }
}
