<?php

class NotificationsCest
{
    protected $organizationsEndpoint = '/api/v1/organizations';
    protected $userEndpoint = '/api/v1/users/me';
    protected $rollcallsEndpoint = '/api/v1/rollcalls';

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

    /*
     * Ensure I get a notification if I am a recipient of a new rollcall
     *
     */
    public function receiveRollCallReceivedNotification(ApiTester $I)
    {
        $org_id = 2;
        $message = 'Westgate under siege, are you ok?';
        $I->wantTo('When a rollcall is received, I get a notification as a recipient');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost($this->rollcallsEndpoint, [
            'message' => $message,
            'organization_id' => $org_id,
            'recipients' => [
                [
                    'id' => 3
                ],
                [
                    'id' => 1
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->amAuthenticatedAsOrgOwner();
        $I->sendGet($this->userEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'notifications' => [
                'type' => 'RollCall\\Notifications\\RollCallReceived',
                'data' => [
                  'rollcall_message' => $message,
                ]
            ]
        ]);
    }

    /*
     * Ensure I get a notification if I am a recipient of a rollcall and someone replies
     *
     */
    public function receiveReplyReceivedNotification(ApiTester $I)
    {
        $org_id = 2;
        $rollcall_id = 1;
        $I->wantTo('When a reply to a rollcall is received, I get a notification as a recipient');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost($this->rollcallsEndpoint.'/'.$rollcall_id.'/replies', [
            'message'  => 'Test response',
            'answer'   => 'yes'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->amAuthenticatedAsOrgOwner();
        $I->sendGet($this->userEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'notifications' => [
                'type' => 'RollCall\\Notifications\\ReplyReceived',
                'data' => [
                  'rollcall_id' => $rollcall_id,
                  'reply_from' => 'Org admin',
                ]
            ]
        ]);
    }

}
