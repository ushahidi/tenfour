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
    $api->post($base.'organizations/{organization}/people/{member}/invite', ['uses' => 'PersonController@invitePerson']);
    $api->post($base.'organizations/{organization}/people/owner/notify', ['uses' => 'PersonController@notifyOwner']);
    // $api->post($base.'organizations/{organization}/people/{member}/notify', ['uses' => 'PersonController@notifyPerson']);

    // Org member contacts
    $api->resource($base.'organizations/{organization}/people/{person}/contacts', 'PersonContactController');

    //Org contacts file uploads
    $api->post($base.'organizations/{organization}/files', ['uses' => 'ContactFilesController@create']);
    $api->put($base.'organizations/{organization}/files/{file}', ['uses' => 'ContactFilesController@update']);
    $api->post($base. 'organizations/{organization}/files/{file}/contacts', ['uses' => 'ContactFilesController@importContacts']);

    //Org groups
    $api->resource($base.'organizations/{organization}/groups', 'GroupController');

    // Supported regions for an org
    $api->get($base.'organizations/{organization}/regions', ['uses' => 'RegionController@all']);

    //checkins
    ////////////////////////////////////////////////////////////////////
    $api->get($base. 'checkins', ['as' => 'rollcalls.index', 'uses' => 'RollCallController@all']);
    $api->post($base.'checkins', ['as' => 'rollcalls.create', 'uses' => 'RollCallController@create']);
    $api->get($base. 'checkins/{rollcall}', ['as' => 'rollcalls.show', 'uses' => 'RollCallController@find']);
    $api->put($base. 'checkins/{rollcall}', ['as' => 'rollcalls.update', 'uses' => 'RollCallController@update']);

    $api->get($base.'checkins/{rollcall}/messages', ['uses' => 'RollCallController@listMessages']);
    $api->get($base.'checkins/{rollcall}/recipients', ['uses' => 'RollCallController@listRecipients']);
    $api->post($base.'checkins/{rollcall}/recipients/{recipient}/messages', ['uses' => 'RollCallController@addMessage']);

    // Rollcall Replies
    $api->put($base.'checkins/{rollcall}/replies/{reply}', ['uses' => 'ReplyController@update']);
    $api->get($base.'checkins/{rollcall}/replies/{reply}', ['uses' => 'ReplyController@find']);
    $api->get($base.'checkins/{rollcall}/replies', ['uses' => 'ReplyController@listReplies']);
    $api->post($base.'checkins/{rollcall}/replies', ['uses' => 'ReplyController@addReply']);

    // Subscriptions
    $api->resource($base.'organizations/{organization}/subscriptions', 'SubscriptionController');
    $api->post($base.'organizations/{organization}/subscriptions/hostedpage', ['uses' => 'SubscriptionController@createHostedPage']);
    $api->put($base.'organizations/{organization}/subscriptions/hostedpage/{subscription}', ['uses' => 'SubscriptionController@updateHostedPage']);
    $api->post($base.'organizations/{organization}/subscriptions/hostedpagesuccess/{subscription}', ['uses' => 'SubscriptionController@confirmHostedPage']);

});
