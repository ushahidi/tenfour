<?php

/* Test MOs pushed by the provider to webhooks */

class SMSCest
{
    public function receiveAfricasTalkingMOs(ApiTester $I)
    {
        $endpoint = 'sms/receive/africastalking';
        $I->wantTo('Receive MOs from Africa\'s Talking');
        $I->sendPost($endpoint, [
            'from' => '254721674180',
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
            'msisdn' => '254721674180',
            'text' => 'test MO from Nexmo',
            'messageId' => '000000FFFB0356D1',
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('Hi');
    }


    public function receiveNexmoMOsWithValidSignature(ApiTester $I)
    {
        $secret = $I->getApplication()->config->get('tenfour.messaging.nexmo_security_secret');

        // Current timestamp in UTC + 0
        $params = [
            'msisdn' => '254721674180',
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

    public function receiveResponseReceivedSMS(ApiTester $I)
    {
        $endpoint = 'sms/receive/africastalking';
        $I->wantTo("Receive a 'response received' SMS");
        $I->sendPost($endpoint, [
            'from'  => '254792999999',
            'to'    => '20880',
            'text'  => 'Test get a response received SMS',
            'id'    => '11111123',
        ]);

        $I->seeRecord('replies', [
            'message' => 'Test get a response received SMS',
            'message_id' => '11111123',
            'check_in_id' => 1,
        ]);

        $I->seeRecord('outgoing_sms_log', [
            'message' => "TenFour has received your response.\n",
            'type'    => 'response_received',
            'to'      => '+254792999999',
            'from'    => '20880'
        ]);

        $I->seeResponseCodeIs(200);
    }

    public function receiveResponseReceivedSMSAnotherNumber(ApiTester $I)
    {
        $endpoint = 'sms/receive/africastalking';
        $I->wantTo("Receive a 'response received' SMS from another outgoing number");
        $I->sendPost($endpoint, [
            'from'  => '254792999999',
            'to'    => '20881',
            'text'  => 'Test get a response received SMS from another outgoing number',
            'id'    => '1111112345',
        ]);

        $I->seeRecord('replies', [
            'message' => 'Test get a response received SMS from another outgoing number',
            'message_id' => '1111112345',
            'check_in_id' => 2,
        ]);

        $I->seeRecord('outgoing_sms_log', [
            'message' => "TenFour has received your response.\n",
            'type'    => 'response_received',
            'to'      => '+254792999999',
            'from'    => '20881'
        ]);

        $I->seeResponseCodeIs(200);
    }
}
