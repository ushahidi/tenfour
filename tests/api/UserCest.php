<?php

class UserCest
{
    protected $endpoint = '/api/v1/users';

    /*
     * Get all users as an admin
     *
     */
    public function getAllUsers(ApiTester $I)
    {
        $I->wantTo('Get a list of all users as admin');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'users' => [
                [
                    'name'        => 'Test user',
                    'person_type' => 'member'
                ],
                [
                    'name'        => 'Admin user',
                    'person_type' => 'member'
                ]
            ]
        ]);
    }

    /*
     * Attempt to get all users as a user
     *
     */
    public function getAllUsersAsUser(ApiTester $I)
    {
        $I->wantTo('Get a list of all users as a user');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();

    }

    /*
     * Get user details as a user
     *
     */
    public function getUser(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get user details as a user');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'user' => [
                'name'        => 'Test user',
                'person_type' => 'member'
            ]
        ]);
    }

    /*
     * Get user details as 'me'
     *
     */
    public function getUserAsMe(ApiTester $I)
    {
        $id = 'me';
        $I->wantTo('Get user details as a user');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'user' => [
                'name'        => 'Test user',
                'person_type' => 'member'
            ]
        ]);
    }


    /*
     * Attempt to get user details as an anonymous user
     *
     *//*
    public function getUserAsAnonUser(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get a user as an anonymous user');
        $I->amAuthenticatedAsClient();
        $I->sendGET($this->endpoint."/$id");
        $I->seeResponseCodeIs(403);
    }

    /*
     * Create user as client
     *
     */
    public function createUser(ApiTester $I)
    {
        $I->wantTo('Create a user as client');
        $I->amAuthenticatedAsClient();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'name' => 'Nat Manning',
            'password' => 'dancer01',
            'password_confirm' => 'dancer01',
            'person_type' => 'user',
            'config_profile_reviewed' => true,
            'config_self_test_sent' => false
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'user' => [
                'name'        => 'Nat Manning',
                'person_type' => 'user'
            ]
        ]);
    }

    /*
     * Update user details as the user
     *
     */
    public function updateUser(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Update user details as the user');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'id' => $id,
            'name' => 'Team RollCall',
            'password' => 'rollcall',
            'person_type' => 'user'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'user' => [
                'name'        => 'Team RollCall',
                'person_type' => 'user'
            ]
        ]);
    }

    /*
     * Delete user details as user
     *
     */
    public function deleteUser(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Delete a user');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
    }

    /*
     * Change a user's password
     *
     */
    public function changePassword(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Change a user\'s pasword');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'password' => 'another_password',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->sendPOST('/oauth/access_token', [
            'client_id' => 'webapp',
            'client_secret' => 'secret',
            'scope' => 'user',
            'username' => 'test@ushahidi.com',
            'password' => 'another_password',
            'grant_type' => 'password'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    /*
     * Reset password
     *
     */
    public function resetPassword(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Reset my password');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/password/email', [
            'username' => 'test@ushahidi.com',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $record = $I->grabRecord('password_resets', array('email' => 'test@ushahidi.com'));
        $I->sendPOST('/password/reset', [
            'username' => 'test@ushahidi.com',
            'password' => 'cake1234',
            'password_confirmation' => 'cake1234',
            'token' => $record['token']
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->sendPOST('/oauth/access_token', [
            'client_id' => 'webapp',
            'client_secret' => 'secret',
            'scope' => 'user',
            'username' => 'test@ushahidi.com',
            'password' => 'cake1234',
            'grant_type' => 'password'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

}
