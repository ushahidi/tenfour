<?php

class ContactCest
{
    protected $endpoint = '/api/v1/contacts';

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
        $I->seeResponseContainsJson([
            'contacts' => [
                [
                    'can_receive' => 1,
                    'type' => 'phone',
                    'contact' => '0721674180',
                    'user' => [
                        'id'   => 1,
                        'name' => 'Test user'
                    ],
                ],
                [
                    'can_receive' => 1,
                    'type' => 'email',
                    'contact' => 'test@ushahidi.com',
                    'user' => [
                        'id'   => 1,
                        'name' => 'Test user'
                    ]
                ],
                [
                    'can_receive' => 0,
                    'type' => 'email',
                    'contact' => 'linda@ushahidi.com',
                    'user' => [
                        'id'   => 2,
                        'name' => 'Admin user'
                    ]
                ],
                [
                    'can_receive' => 0,
                    'type' => 'phone',
                    'contact' => '0792999999',
                    'user' => [
                        'id'   => 4,
                        'name' => 'Org owner'
                    ]
                ]
            ]
        ]);
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
            'can_receive' => 1,
            'type' => 'email',
            'contact' => 'test@ushahidi.com',
            'user_id' => 3
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'contact' => [
                'can_receive' => 1,
                'type' => 'email',
                'contact' => 'test@ushahidi.com',
                'user' => [
                    'id' => 3
                ]
            ]
        ]);
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
        $I->seeResponseContainsJson([
            'contact' => [
                'can_receive' => 0,
                'type' => 'email',
                'contact' => 'test@ushahidi.com',
                'user' => [
                    'id' => 3
                ]
            ]
        ]);
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
        $I->seeResponseContainsJson([
            'contact' => [
                'can_receive' => 0,
                'type' => 'email',
                'contact' => 'test@ushahidi.com',
                'user' => ['id' => 3]
            ]
        ]);
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
