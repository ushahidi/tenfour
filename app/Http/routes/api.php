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
    //Users
    ////////////////////////////////////////////////////////////////////
    $api->get($base.'users', ['as' => 'users.index', 'uses' => 'UserController@all']);
    $api->post($base.'users', ['as' => 'users.create', 'uses' => 'UserController@create']);
    $api->get($base. 'users/{user}', ['as' => 'users.show', 'uses' => 'UserController@find']);
    $api->put($base. 'users/{user}', ['as' => 'users.update', 'uses' => 'UserController@update']);
    $api->delete($base. 'users/{user}', ['as' => 'users.delete', 'uses' => 'UserController@delete']);

    //Organizations
    ////////////////////////////////////////////////////////////////////

    $api->resource($base.'organizations', 'OrganizationController');

    // Org members
    $api->resource($base.'organizations/{organization}/members', 'MemberController');

    // Org member contacts
    $api->resource($base.'organizations/{organization}/members/{member}/contacts', 'MemberContactController');

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
    $api->get($base. 'rollcalls/{rollcall}', ['as' => 'rollcalls.show', 'uses' => 'RollCallController@find']);
    $api->put($base. 'rollcalls/{rollcall}', ['as' => 'rollcalls.update', 'uses' => 'RollCallController@update']);

    $api->get($base.'rollcalls/{rollcall}/messages', ['uses' => 'RollCallController@listMessages']);
    $api->get($base.'rollcalls/{rollcall}/recipients', ['uses' => 'RollCallController@listRecipients']);

    // Rollcall Replies
    $api->put($base.'rollcalls/{rollcall}/replies/{reply}', ['uses' => 'ReplyController@update']);
    $api->get($base.'rollcalls/{rollcall}/replies/{reply}', ['uses' => 'ReplyController@find']);
    $api->get($base.'rollcalls/{rollcall}/replies', ['uses' => 'ReplyController@listReplies']);
    $api->post($base.'rollcalls/{rollcall}/replies', ['uses' => 'ReplyController@addReply']);
});
