<?php

class UserCest
{
    protected $endpoint = '/users';

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
                ['name' => 'Test user',
                 'email' => 'test@ushahidi.com'
                ],
                [
                'name' => 'Admin user',
                'email' => 'admin@ushahidi.com'
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
                'name' => 'Test user',
                'email' => 'test@ushahidi.com'
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
                'name' => 'Test user',
                'email' => 'test@ushahidi.com'
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
            'email' => 'nat@ushahidi.com',
            'password' => 'dancer01',
            'password_confirm' => 'dancer01'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'user' => [
                'name' => 'Nat Manning',
                'email' => 'nat@ushahidi.com'
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
            'name' => 'Team RollCall',
            'email' => 'rollcall@ushahidi.com',
            'password' => 'rollcall',
            'password_confirm' => 'rollcall'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'user' => [
                'name' => 'Team RollCall',
                'email' => 'rollcall@ushahidi.com'
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

}
