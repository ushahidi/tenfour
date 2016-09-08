<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class ApiHelper extends \Codeception\Module
{
    protected $client_token = 'anonusertoken';
    protected $user_token = 'usertoken';
    protected $admin_token = 'admintoken';
    protected $orgadmin_token = 'orgadmintoken';
    protected $orgowner_token = 'orgownertoken';
    
    public function amAuthenticatedAsClient()
    {
        $this->getModule('REST')->amBearerAuthenticated($this->client_token);
    }

    public function amAuthenticatedAsUser()
    {
    	$this->getModule('REST')->amBearerAuthenticated($this->user_token);
    }

    public function amAuthenticatedAsAdmin()
    {
    	$this->getModule('REST')->amBearerAuthenticated($this->admin_token);
    }

    public function amAuthenticatedAsOrgAdmin()
    {
        $this->getModule('REST')->amBearerAuthenticated($this->orgadmin_token);
    }

    public function amAuthenticatedAsOrgOwner()
    {
        $this->getModule('REST')->amBearerAuthenticated($this->orgowner_token);
    }
}