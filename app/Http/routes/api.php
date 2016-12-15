<?php

use Dingo\Api\Routing\Router;

$version = 'v1';
$base = 'api/'.$version.'/';
$api = app(Router::class);

$api->version($version, [
    'namespace' => 'RollCall\Http\Controllers\Api\First',
    'protected' => true,
    'middleware' => 'api.auth',
    'scopes' => ['user','organization', 'contact', 'rollcall']
], function ($api) use ($base) {
    // Authentication
    //////////////////////////////////////////////////////////////////////
    // XXX: handled through oauth endpoint
    //$api->post('auth', ['protected' => false, 'uses' => 'AuthController@token']);

    // Welcome
    /*
    $api->get($base, ['uses' => function () {

    }]);
    */

    //Users
    ////////////////////////////////////////////////////////////////////
    $api->get($base.'users', ['as' => 'users.index', 'uses' => 'UserController@all']);
    $api->post($base.'users', ['as' => 'users.create', 'uses' => 'UserController@create']);
    $api->get($base. 'users/{user}', ['as' => 'users.show', 'uses' => 'UserController@find']);
    $api->put($base. 'users/{user}', ['as' => 'users.update', 'uses' => 'UserController@update']);
    $api->delete($base. 'users/{user}', ['as' => 'users.delete', 'uses' => 'UserController@delete']);

    //Organizations
    ////////////////////////////////////////////////////////////////////

    $api->get($base.'organizations', ['as' => 'organizations.index', 'uses' => 'OrganizationController@all']);
    $api->post($base.'organizations', ['as' => 'organizations.create', 'uses' => 'OrganizationController@create']);
    $api->post($base.'organizations/{organization}/members', ['uses' => 'OrganizationController@addMember']);
    $api->delete($base.'organizations/{organization}/members/{member}', ['uses' => 'OrganizationController@deleteMember']);
    $api->put($base.'organizations/{organization}/members/{member}', ['uses' => 'OrganizationController@updateMember']);
    $api->get($base.'organizations/{organization}/members/{member}', ['uses' => 'OrganizationController@findMember']);
    $api->post($base.'organizations/{organization}/members/{member}/contacts', ['uses' => 'OrganizationController@addContact']);
    $api->put($base.'organizations/{organization}/members/{member}/contacts/{contact}', ['uses' => 'OrganizationController@updateContact']);
    $api->delete($base.'organizations/{organization}/members/{member}/contacts/{contact}', ['uses' => 'OrganizationController@deleteContact']);
    $api->get($base.'organizations/{organization}/members', ['uses' => 'OrganizationController@listMembers']);
    $api->get($base.'organizations/{organization}', ['as' => 'organizations.show', 'uses' => 'OrganizationController@find']);
    $api->put($base.'organizations/{organization}', ['as' => 'organizations.update', 'uses' => 'OrganizationController@update']);
    $api->delete($base. 'organizations/{organization}', ['as' => 'organization.delete', 'uses' => 'OrganizationController@delete']);

    //Contacts
    ////////////////////////////////////////////////////////////////////
    $api->get($base.'contacts', ['as' => 'contacts.index', 'uses' => 'ContactController@all']);
    $api->post($base.'contacts', ['as' => 'contacts.create', 'uses' => 'ContactController@create']);
    $api->put($base. 'contacts/{contact}', ['as' => 'contacts.update', 'uses' => 'ContactController@update']);
    $api->delete($base. 'contacts/{contact}', ['as' => 'contacts.delete', 'uses' => 'ContactController@delete']);


    //Rollcalls
    ////////////////////////////////////////////////////////////////////
    $api->get($base. 'rollcalls', ['as' => 'rollcalls.index', 'uses' => 'RollCallController@all']);
    $api->post($base.'rollcalls', ['as' => 'rollcalls.create', 'uses' => 'RollCallController@create']);

    $api->put($base.'rollcalls/{rollcall}/replies/{reply}', ['uses' => 'ReplyController@update']);
    $api->get($base.'rollcalls/{rollcall}/replies/{reply}', ['uses' => 'ReplyController@find']);
    $api->get($base.'rollcalls/{rollcall}/replies', ['uses' => 'ReplyController@listReplies']);
    $api->post($base.'rollcalls/{rollcall}/replies', ['uses' => 'ReplyController@addReply']);

    $api->get($base.'rollcalls/{rollcall}/messages', ['uses' => 'RollCallController@listMessages']);
    $api->get($base.'rollcalls/{rollcall}/recipients', ['uses' => 'RollCallController@listRecipients']);



    $api->get($base. 'rollcalls/{rollcall}', ['as' => 'rollcalls.show', 'uses' => 'RollCallController@find']);
    $api->put($base. 'rollcalls/{rollcall}', ['as' => 'rollcalls.update', 'uses' => 'RollCallController@update']);
});
