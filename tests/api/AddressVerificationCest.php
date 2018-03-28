<?php

use Illuminate\Support\Facades\Queue;

class AddressVerificationCest
{
    protected $endpoint = '/verification/email';

    /*
     * Send a verification email
     *
     */
    public function sendEmailVerification(ApiTester $I)
    {
        $I->wantTo('Send a verification email for a new email address');
        $I->sendPOST($this->endpoint, [
            'address' => 'john@ushahidi.com',
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
              'address' => 'john@ushahidi.com',
        ]);

        $I->seeRecord('outgoing_mail_log', [
            'subject'     => "Verify your TenFour email address",
            'type'        => 'verification',
            'to'          => 'john@ushahidi.com',
        ]);

    }

    /*
     * Verify email address
     *
     */
    public function verifyEmail(ApiTester $I)
    {
        $endpoint = $this->endpoint . '/?address=mary@ushahidi.com&token=token';
        $I->wantTo('I want to verify an email address');
        $I->sendGET($endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->cantSeeInDatabase('unverified_addresses', [
            'address' => 'mary@ushahidi.com'
        ]);
        $I->seeResponseContainsJson([
              'address' => 'mary@ushahidi.com',
              'id'      => 1,
        ]);
    }
}
