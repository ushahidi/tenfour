<?php

use Codeception\Util\Fixtures;

class MailCest
{
    public function handleSESPermanentBounce(ApiTester $I)
    {
        $bounce = Fixtures::get('permanent_bounce');

        $bounce_threshold = config('rollcall.messaging.bounce_threshold');

        $endpoint = 'ses/bounces';
        $I->wantTo('Handle permanent SES bounces');
        $I->haveHttpHeader('x-amz-sns-message-type', 'Notification');
        $I->sendPost($endpoint, $bounce);

        $I->seeRecord('contacts', [
            'contact' => 'linda@ushahidi.com',
            'bounce_count' => $bounce_threshold,
        ]);

        $I->seeResponseCodeIs(200);
    }

    public function handleSESTransientBounce(ApiTester $I)
    {
        $bounce = Fixtures::get('transient_bounce');
        $count = 1;

        $endpoint = 'ses/bounces';
        $I->wantTo('Handle transient SES bounces');
        $I->haveHttpHeader('x-amz-sns-message-type', 'Notification');
        $I->sendPost($endpoint, $bounce);

        $I->seeRecord('contacts', [
            'contact' => 'linda@ushahidi.com',
            'bounce_count' => $count,
        ]);

        $I->seeResponseCodeIs(200);
    }

    public function handleSESComplaint(ApiTester $I)
    {
        $complaint = Fixtures::get('complaint');
        $count = 1;

        $endpoint = 'ses/complaints';
        $I->wantTo('Handle SES complaints');
        $I->haveHttpHeader('x-amz-sns-message-type', 'Notification');
        $I->sendPost($endpoint, $complaint);

        $I->seeRecord('roll_calls', [
            'id' => '1',
            'complaint_count' => $count,
        ]);

        $I->seeResponseCodeIs(200);
    }
}
