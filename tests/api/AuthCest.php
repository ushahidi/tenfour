<?php

class AuthCest
{
    protected $endpoint = '/oauth/token';

    /*
     * Get an auth token
     */
    public function getAuthTokenWithPasswordGrant(ApiTester $I)
    {
        $I->wantTo('Get an oauth token with password grant');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'client_id' => '1',
            'client_secret' => 'secret',
            'scope' => 'user',
            'username' => 'tenfourtest:admin@ushahidi.com',
            'password' => 'westgate',
            // 'subdomain' => 'tenfourtest',
            'grant_type' => 'password'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "token_type" => "Bearer",
            "expires_in" => 31536000 // test might fail because sometimes this value is 31535999 
        ]);
        $I->seeResponseMatchesJsonType([
            'access_token' => 'string',
            'token_type' => 'string',
            'expires_in' => 'integer',
        ]);
    }

    /*
     *  Try to get a token with invalid credentials
     */
    public function getAuthTokenWithInvalidCredentials(ApiTester $I)
    {
        $I->wantTo('Get an oauth token with invalid credentials');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'client_id' => '1',
            'client_secret' => 'secret',
            'scope' => 'user',
            'username' => 'tenfourtest:admin@ushahidi.com',
            'password' => 'invalid',
            // 'subdomain' => 'tenfourtest',
            'grant_type' => 'password'
        ]);
        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "message" => 'The user credentials were incorrect.',
            "error" => 'invalid_credentials'
        ]);
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'error' => 'string',
        ]);
    }

    /*
     * Get an auth token
     */
    public function getAuthTokenWithClientGrant(ApiTester $I)
    {
        $I->wantTo('Get an oauth token with client creds');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'client_id' => '1',
            'client_secret' => 'secret',
            'scope' => 'user',
            'grant_type' => 'client_credentials'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "token_type" => "Bearer",
            "expires_in" => 31622400
        ]);
        $I->seeResponseMatchesJsonType([
            'access_token' => 'string',
            'token_type' => 'string',
            'expires_in' => 'integer',
        ]);
    }
}
