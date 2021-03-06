<?php

use Codeception\Util\Fixtures;

class MailCest
{
    public function handleReceiveSelfTestCheckIn(ApiTester $I)
    {
        $user_id = 1;
        $contact_id = 2;

        $reply = Fixtures::get('self_test_reply');
        $endpoint = 'mail/receive';

        $I->wantTo('Test a reply to a self test check-in');
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

        $bounce_threshold = config('tenfour.messaging.bounce_threshold');

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

        $I->seeRecord('check_ins', [
            'id' => '1',
            'complaint_count' => $count,
        ]);

        // check a notification has been sent
        $I->seeRecord('notifications', [
            'notifiable_id'           => '4',
            'notifiable_type'         => 'TenFour\Models\User',
            'type'                    => 'TenFour\Notifications\Complaint',
            'data'                    => '{"person_name":"Admin user","person_id":2,"profile_picture":false,"initials":"AU","check_in_message":"Westgate under siege","check_in_id":1}'
        ]);
    }

    public function receiveCheckInMail(ApiTester $I)
    {
        $I->wantTo('Receive a check-in mail');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/api/v1/organizations/2/checkins', [
            'message' => 'Sinkhole has opened. Are you ok?',
            'organization_id' => 2,
            'send_via' => ['email'],
            'recipients' => [
                [
                    'id' => 3
                ]
            ],
            'answers' => [
              ['answer'=>'No','color'=>'#BC6969','icon'=>'icon-exclaim','type'=>'negative'],
              ['answer'=>'Yes','color'=>'#E8C440','icon'=>'icon-check','type'=>'positive']
            ]
        ]);

        $I->seeRecord('outgoing_mail_log', [
            'subject'     => "Sinkhole has opened. Are you ok?",
            'type'        => 'check_in',
            'to'          => 'org_member@ushahidi.com',
            'check_in_id' => 10,
            'from'        => 'checkin+10@qa.tenfour.org'
        ]);
    }
}
