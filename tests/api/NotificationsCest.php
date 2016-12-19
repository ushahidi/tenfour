<?php

class NotificationsCest
{
    protected $organizationsEndpoint = '/api/v1/organizations';
    protected $userEndpoint = '/api/v1/users/me';

    /*
     * Ensure that admins receive a notification when a person is added to the organization
     *
     */
    public function receivePersonJoinedOrganizationNotification(ApiTester $I)
    {
        $org_id = 2;
        $I->wantTo('When a person joins the organization, I get a notification as an admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost($this->organizationsEndpoint."/$org_id/people", [
            'name' => 'Mary',
            'email' => 'mary@rollcall.io',
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendGet($this->userEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'notifications' => [
                'type' => 'RollCall\\Notifications\\PersonJoinedOrganization',
                'data' => [
                  'person_name' => 'Mary',
                ]
            ]
        ]);
    }

    /*
     * Ensure that members don't receive a notification when a person is added to the organization
     *
     */
    public function dontReceivePersonJoinedOrganizationNotification(ApiTester $I)
    {
        $org_id = 2;
        $I->wantTo('When a person joins the organization, I do not get a notification as a user');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost($this->organizationsEndpoint."/$org_id/people", [
            'name' => 'Mary',
            'email' => 'mary@rollcall.io',
        ]);
        $I->seeResponseCodeIs(200);

        $I->amAuthenticatedAsUser();
        $I->sendGet($this->userEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseContainsJson([
            'notifications' => [
              'type' => 'RollCall\\Notifications\\PersonJoinedOrganization',
              'data' => [
                  'person_name' => 'Mary',
                ]
            ]
        ]);
    }

    /*
     * Ensure that admins receive a notification when a person left the organization
     *
     */
    public function receivePersonLeftOrganizationNotification(ApiTester $I)
    {
        $org_id = 2;
        $user_id = 3;
        $I->wantTo('When a person leaves the organization, I get a notification as an admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->organizationsEndpoint."/$org_id/people/$user_id");
        $I->seeResponseCodeIs(200);

        $I->sendGet($this->userEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'notifications' => [
                'type' => 'RollCall\\Notifications\\PersonLeftOrganization',
                'data' => [
                  'person_name' => 'Org member',
                ]
            ]
        ]);
    }


    /*
     * Ensure that members don't receive a notification when a person left the organization
     *
     */
    public function dontReceivePersonLeftOrganizationNotification(ApiTester $I)
    {
        $org_id = 2;
        $user_id = 3;
        $I->wantTo('When a person leaves the organization, I get a notification as an admin');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->organizationsEndpoint."/$org_id/people/$user_id");
        $I->seeResponseCodeIs(200);

        $I->amAuthenticatedAsUser();
        $I->sendGet($this->userEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseContainsJson([
            'notifications' => [
                'type' => 'RollCall\\Notifications\\PersonLeftOrganization',
                'data' => [
                  'person_name' => 'Org member',
                ]
            ]
        ]);
    }

}
