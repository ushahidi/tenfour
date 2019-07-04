<?php

class DeviceTokenCest
{
    protected $endpoint = '/api/v1/organizations';

    public function createDeviceToken(ApiTester $I)
    {
        $orgId = 2;
        $id = 1;
        $token = 'testtoken';

        $I->wantTo('Register a device token for a user');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST($this->endpoint."/$orgId/people/$id/tokens", [
            'token' => $token
        ]);
        $I->seeResponseCodeIs(200);

        $I->seeRecord('device_tokens', [
            'user_id'       => $id,
            'token'         => $token,
        ]);

        $I->sendPOST($this->endpoint."/$orgId/people/$id/tokens", [
            'token' => $token
        ]);
        $I->seeResponseCodeIs(200);
    }

    public function deleteDeviceToken(ApiTester $I)
    {
        $orgId = 2;
        $id = 1;
        $token = 'testtoken';

        $I->wantTo('Delete a device token for a user');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST($this->endpoint."/$orgId/people/$id/tokens", [
            'token' => $token
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendDELETE($this->endpoint."/$orgId/people/$id/tokens/".$token);
        $I->seeResponseCodeIs(200);

        $I->dontSeeRecord('device_tokens', [
            'user_id'       => $id,
            'token'         => $token,
        ]);
    }

}
