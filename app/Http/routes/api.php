<?php

use Dingo\Api\Routing\Router;

$version = 'v1';
$base = 'api/'.$version.'/';
$api = app(Router::class);

// Add routes with anonymous access
$api->version($version, [
    'namespace' => 'RollCall\Http\Controllers\Api\First'
], function ($api) use ($base) {
    $api->get($base.'organizations', 'OrganizationController@index');
});

// Add authenticated routes
$api->version($version, [
    'namespace' => 'RollCall\Http\Controllers\Api\First',
    'protected' => true,
    'middleware' => 'api.auth',
    'scopes' => ['user','organization', 'contact', 'rollcall']
], function ($api) use ($base) {
    //Organizations
    ////////////////////////////////////////////////////////////////////
    $api->resource($base.'organizations', 'OrganizationController', ['except' => ['index']]);

    // Org members
    $api->resource($base.'organizations/{organization}/people', 'PersonController');
    $api->get($base.'organizations/{organization}/people/{member}/invite', ['uses' => 'PersonController@invitePerson']);

    // Org member contacts
    $api->resource($base.'organizations/{organization}/people/{person}/contacts', 'PersonContactController');

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
