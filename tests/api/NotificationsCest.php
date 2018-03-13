<?php

class NotificationsCest
{
    protected $organizationsEndpoint = '/api/v1/organizations';
    protected $peopleEndpoint = '/api/v1/organizations/2/people/me';
    protected $checkInsEndpoint = '/api/v1/organizations/2/checkins';

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
            'email' => 'mary@tenfour.org',
        ]);
        $I->seeResponseCodeIs(200);

        $I->sendGet($this->peopleEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'notifications' => [
                'type' => 'PersonJoinedOrganization',
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
            'email' => 'mary@tenfour.org',
        ]);
        $I->seeResponseCodeIs(200);

        $I->amAuthenticatedAsUser();
        $I->sendGet($this->peopleEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseContainsJson([
            'notifications' => [
              'type' => 'PersonJoinedOrganization',
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

        $I->sendGet($this->peopleEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'notifications' => [
                'type' => 'PersonLeftOrganization',
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
        $I->sendGet($this->peopleEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseContainsJson([
            'notifications' => [
                'type' => 'PersonLeftOrganization',
                'data' => [
                  'person_name' => 'Org member',
                ]
            ]
        ]);
    }

    /*
     * Ensure I get a notification if I am a recipient of a new check-in
     *
     */
    public function receiveRollCallReceivedNotification(ApiTester $I)
    {
        $org_id = 2;
        $message = 'Westgate under siege, are you ok?';
        $I->wantTo('When a check-in is received, I get a notification as a recipient');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost($this->checkInsEndpoint, [
            'message' => $message,
            'organization_id' => $org_id,
            'send_via' => ['apponly'],
            'recipients' => [
                [
                    'id' => 3
                ],
                [
                    'id' => 1
                ],
                [
                    'id' => 4
                ]
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->amAuthenticatedAsOrgOwner();
        $I->sendGet($this->peopleEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'notifications' => [
                'type' => 'CheckInReceived',
                'data' => [
                  'check_in_message' => $message
                ]
            ]
        ]);
    }

    /*
     * Ensure I get a notification if I am a recipient of a check-in and someone replies
     *
     */
    public function receiveReplyReceivedNotification(ApiTester $I)
    {
        $org_id = 2;
        $check_in_id = 1;
        $I->wantTo('When a reply to a check-in is received, I get a notification as a recipient');
        $I->amAuthenticatedAsOrgAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost($this->checkInsEndpoint.'/'.$check_in_id.'/replies', [
            'message'  => 'Test response',
            'answer'   => 'yes'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->amAuthenticatedAsOrgOwner();
        $I->sendGet($this->peopleEndpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'notifications' => [
                'type' => 'ReplyReceived',
                'data' => [
                  'check_in_id' => $check_in_id,
                  'reply_from' => 'Org admin',
                ]
            ]
        ]);
    }

}
