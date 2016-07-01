<?php

class ContactCest
{
    protected $endpoint = '/contacts';

    /*
     * Get all contacts as an admin
     *
     */
    public function getAllContacts(ApiTester $I)
    {
        $I->wantTo('Get a list of all contacts as an admin');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            ['user_id' => 1,'can_receive' => 1, 'type' => 'phone','contact' => '0721674180']
        );
		$I->seeResponseContainsJson(
			['user_id' => 2,'can_receive' => 0, 'type' => 'email', 'contact' => 'linda@ushahidi.com']
		);

    }
    /*
     * Create contact as admin
     *
     */
    public function createContact(ApiTester $I)
    {
        $I->wantTo('Create a contact as admin');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'user_id' => 3,
            'can_receive' => 1,
            'type' => 'email',
            'contact' => 'test@ushahidi.com'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['contact' =>
            ['user_id' => 3,'can_receive' => 1, 'type' => 'email', 'contact' => 'test@ushahidi.com']
        ]
        );
    }
    /*
     * Update contact details as the admin
     *
     */
    public function updateContact(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Update contact details as the admin');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'user_id' => 3,
            'can_receive' => 0,
            'type' => 'email',
            'contact' => 'test@ushahidi.com'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['contact' =>
            ['user_id' => 3, 'can_receive' => 0,'type' => 'email', 'contact' => 'test@ushahidi.com']
		]
        );
    }

    /*
     * Update contact details as the user
     *
     */
    public function updateContactAsUser(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Update contact details as the user');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'user_id' => 3,
            'can_receive' => 0,
            'type' => 'email',
            'contact' => 'test@ushahidi.com'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['contact' =>
            ['user_id' => 3,'can_receive' => 0,'type' => 'email', 'contact'=> 'test@ushahidi.com']
		]
        );
    }
    /*
     * Delete contact as an admin
     *
     */
    public function deleteContact(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Delete a contact');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
    }
}
