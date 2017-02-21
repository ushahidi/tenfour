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


    public function receiveNexmoMOsWithoutSignature(ApiTester $I)
    {
        $endpoint = 'sms/receive/nexmo';
        $I->wantTo('Receive MOs from Nexmo without a valid signature');
        $I->sendPost($endpoint, [
            'msisdn' => '0721674180',
            'text' => 'test MO from Nexmo',
            'messageId' => '000000FFFB0356D1',
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('Hi');
    }


    public function receiveNexmoMOsWithValidSignature(ApiTester $I)
    {
        $secret = $I->getApplication()->config->get('rollcall.messaging.nexmo_security_secret');

        // Current timestamp in UTC + 0
        $params = [
            'msisdn' => '0721674180',
            'text' => 'test MO from Nexmo',
            'messageId' => '000000FFFB0356D1',
            'message-timestamp' => date('Y-m-d H:i:s', (time() - date('Z'))),
        ];

        ksort($params);

        $params = $params + [
            'sig' => md5('&' .urldecode(http_build_query($params)) . $secret)
        ];

        $endpoint = 'sms/receive/nexmo';

        $I->wantTo('Receive MOs from Nexmo with a valid signature');

        $I->sendPost($endpoint, $params);

        $I->seeRecord('replies', [
            'message' => 'test MO from Nexmo',
            'message_id' => '000000FFFB0356D1',
        ]);


        $I->seeResponseCodeIs(200);
    }
}
