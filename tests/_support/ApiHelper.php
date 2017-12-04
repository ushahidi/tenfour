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
    protected $author_token = 'authortoken';
    protected $viewer_token = 'viewertoken';
    protected $chargebee_username = 'chargebee';
    protected $chargebee_password = 'westgate';

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

    public function amAuthenticatedAsAuthor()
    {
        $this->getModule('REST')->amBearerAuthenticated($this->author_token);
    }

    public function amAuthenticatedAsViewer()
    {
        $this->getModule('REST')->amBearerAuthenticated($this->viewer_token);
    }

    public function amAuthenticatedAsChargeBee()
    {
        $this->getModule('REST')->amHttpAuthenticated($this->chargebee_username, $this->chargebee_password);
    }

}
