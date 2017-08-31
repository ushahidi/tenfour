<?php

use Codeception\Util\Fixtures;

class MailCest
{
    public function handleReceiveSelfTestRollCall(ApiTester $I)
    {
        $user_id = 1;
        $contact_id = 2;

        $reply = Fixtures::get('self_test_reply');
        $endpoint = 'mail/receive';

        $I->wantTo('Test a reply to a self test RollCall');
        $I->haveHttpHeader('x-amz-sns-message-type', 'Notification');
        $I->sendPost($endpoint, $reply);
        $I->seeResponseCodeIs(200);

        $I->seeRecord('replies', [
            'message' => 'Confirmed',
            'contact_id' => $contact_id,
            'user_id' => $user_id,
        ]);

        $I->seeRecord('users', [
            'id' => $user_id,
            'config_self_test_sent' => 1
        ]);
    }

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

    public function handleSESOutOfOffice(ApiTester $I)
    {
        $bounce = Fixtures::get('out_of_office_bounce');
        $count = 0;

        $endpoint = 'ses/bounces';
        $I->wantTo('Handle out-of-office SES bounces');
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
        $I->seeResponseCodeIs(200);

        $I->seeRecord('roll_calls', [
            'id' => '1',
            'complaint_count' => $count,
        ]);

        // check a notification has been sent
        $I->seeRecord('notifications', [
            'notifiable_id'           => '4',
            'notifiable_type'         => 'RollCall\Models\User',
            'type'                    => 'RollCall\Notifications\Complaint',
            'data'                    => '{"person_name":"Admin user","person_id":2,"profile_picture":false,"initials":"AU","rollcall_message":"Westgate under siege","rollcall_id":1}'
        ]);
    }
}
