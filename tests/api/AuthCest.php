<?php

class AuthCest
{
    protected $endpoint = '/oauth/access_token';

    /*
     * Get an auth token
     */
    public function getAuthTokenWithPasswordGrant(ApiTester $I)
    {
        $I->wantTo('Get an oauth token with password grant');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'client_id' => 'webapp',
            'client_secret' => 'secret',
            'scope' => 'user',
            'username' => 'admin@ushahidi.com',
            'password' => 'westgate',
            'subdomain' => 'rollcall',
            'grant_type' => 'password'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "token_type" => "Bearer",
            "expires_in" => 3600
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
        $I->wantTo('Get an oauth token with password grant');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'client_id' => 'webapp',
            'client_secret' => 'secret',
            'scope' => 'user',
            'username' => 'admin@ushahidi.com',
            'password' => 'invalid',
            'subdomain' => 'rollcall',
            'grant_type' => 'password'
        ]);
        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "message" => 'The user credentials were incorrect.',
            "status_code" => 401
        ]);
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'status_code' => 'integer',
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
            'client_id' => 'webapp',
            'client_secret' => 'secret',
            'scope' => 'user',
            'grant_type' => 'client_credentials'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "token_type" => "Bearer",
            "expires_in" => 3600
        ]);
        $I->seeResponseMatchesJsonType([
            'access_token' => 'string',
            'token_type' => 'string',
            'expires_in' => 'integer',
        ]);
    }
}
