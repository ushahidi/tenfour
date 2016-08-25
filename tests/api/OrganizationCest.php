<?php


class OrganizationCest
{
    protected $endpoint = '/organizations';

    /*
     * Get all organizations as an admin
     *
     *//*
    public function getAllOrganizations(ApiTester $I)
    {
        $I->wantTo('Get a list of all organizations as an admin');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['name' => 'Test organization','url' => 'test@rollcall.io']);
        $I->seeResponseContainsJson(['name' => 'RollCall','url' => 'rollcall@rollcall.io']);
    }
    /*
     * Get organization details as an admin
     *
     *//*
    public function getOrganization(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get organization details as an admin');
        $I->amAuthenticatedAsAdmin();
        $I->sendGET($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['name' => 'Test organization', 'url' => 'test@rollcall.io']);
    }
    /*
     * Get organization details as an existing user
     *
     *//*
    public function getOrganizationAsUser(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Get organization details as an existing user');
        $I->amAuthenticatedAsUser();
        $I->sendGET($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['name' => 'Test organization', 'url' => 'test@rollcall.io']);
    }
    /*
     * Create organization as user
     *
     */
    public function createOrganization(ApiTester $I)
    {
        $I->wantTo('Create an organization as user');
        $I->amAuthenticatedAsUser();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST($this->endpoint, [
            'name' => 'Ushahidi Inc',
            'url' => 'ushahidi@rollcall.io'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        //$I->seeResponseContainsJson(['name' => 'Ushahidi Inc', 'url' => 'ushahidi@rollcall.io']);
    }
    /*
     * Update organization details as the admin
     *
     *//*
    public function updateOrganization(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Update organization details as the admin');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'name' => 'Test organization test',
            'url' => 'test@rollcall.io',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Test organization test',
            'url' => 'test@rollcall.io',
        ]);
    }
    /*
     * Update organization details as the organization admin
     *
     *//*
    public function updateOrganizationAsOrganizationAdmin(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Update organization details as the organization admin');
        $I->amAuthenticatedAsOrganizationAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT($this->endpoint."/$id", [
            'name' => 'Test organization test',
            'url' => 'test@rollcall.io',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Test organization test',
            'url' => 'test@rollcall.io',
        ]);
    }  
    /*
     * Delete organization as an admin
     *
     *//*
    public function deleteOrganization(ApiTester $I)
    {
        $id = 1;
        $I->wantTo('Delete an organization');
        $I->amAuthenticatedAsAdmin();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDelete($this->endpoint."/$id");
        $I->seeResponseCodeIs(200);
    }
    */

}