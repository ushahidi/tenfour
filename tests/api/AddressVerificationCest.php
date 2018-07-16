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
    public function verifyEmailByToken(ApiTester $I)
    {
        $endpoint = $this->endpoint . '/?address=mary@ushahidi.com&token=token';
        $I->wantTo('I want to verify an email address with a token');
        $I->sendGET($endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
              'address' => 'mary@ushahidi.com',
              'id'      => 1,
        ]);
    }

    /*
     * Verify email address
     *
     */
    public function verifyEmailByCode(ApiTester $I)
    {
        $endpoint = $this->endpoint . '/?address=mary@ushahidi.com&code=123456';
        $I->wantTo('I want to verify an email address with a code');
        $I->sendGET($endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
              'address' => 'mary@ushahidi.com',
              'id'      => 1,
        ]);
    }

    /*
     * max code attemps
     *
     */
    public function hitMaximumCodeAttempts(ApiTester $I)
    {
        $I->wantTo('I want to hit the maximum code attempts');

        $endpoint = $this->endpoint . '/?address=mary@ushahidi.com&code=000000';
        $I->sendGET($endpoint);
        $endpoint = $this->endpoint . '/?address=mary@ushahidi.com&code=000000';
        $I->sendGET($endpoint);
        $endpoint = $this->endpoint . '/?address=mary@ushahidi.com&code=000000';
        $I->sendGET($endpoint);

        $endpoint = $this->endpoint . '/?address=mary@ushahidi.com&code=123456';
        $I->sendGET($endpoint);

        $I->seeResponseCodeIs(404);
    }

}
