<?php

use Dingo\Api\Routing\Router;

$version = 'v1';
$base = 'api/'.$version.'/';
$api = app(Router::class);

// Add routes with anonymous access
$api->version($version, [
    'namespace' => 'TenFour\Http\Controllers\Api\First'
], function ($api) use ($base) {
    $api->get($base.'organizations', 'OrganizationController@index');
});

// Add authenticated routes
$api->version($version, [
    'namespace' => 'TenFour\Http\Controllers\Api\First',
    'protected' => true,
    'middleware' => 'api.auth',
    'scopes' => ['user', 'organization', 'contact', 'checkin']
], function ($api) use ($base) {
    //Organizations
    ////////////////////////////////////////////////////////////////////
    $api->resource($base.'organizations', 'OrganizationController', ['except' => ['index']]);

    // Org members
    $api->resource($base.'organizations/{organization}/people', 'PersonController');
    $api->post($base.'organizations/{organization}/people/{member}/invite', ['uses' => 'PersonController@invitePerson']);
    $api->post($base.'organizations/{organization}/people/owner/notify', ['uses' => 'PersonController@notifyOwner']);
    // $api->post($base.'organizations/{organization}/people/{member}/notify', ['uses' => 'PersonController@notifyPerson']);

    // Notifications endpoint
    $api->get($base.'organizations/{organization}/people/{person}/notifications', ['uses' => 'NotificationController@index']);
    $api->put($base.'organizations/{organization}/people/{person}/notifications', ['uses' => 'NotificationController@updateAll']);
    $api->put($base.'organizations/{organization}/people/{person}/notifications/{notification}', ['uses' => 'NotificationController@update']);

    // Org member contacts
    $api->resource($base.'organizations/{organization}/people/{person}/contacts', 'PersonContactController');

    // Device tokens endpoints
    $api->post($base.'organizations/{organization}/people/{person}/tokens', ['uses' => 'DeviceTokenController@store']);
    $api->delete($base.'organizations/{organization}/people/{person}/tokens/{token}', ['uses' => 'DeviceTokenController@delete']);

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
    $api->get($base. 'organizations/{organization}/checkins/scheduled', ['as' => 'scheduled_checkins.index','uses' => 'ScheduledCheckinController@pending']);
    $api->delete($base. 'organizations/{organization}/checkins/scheduled/{id}', ['uses' => 'ScheduledCheckinController@delete']);

    $api->get($base. 'organizations/{organization}/checkins', ['as' => 'checkins.index','uses' => 'CheckInController@all']);
    
    $api->post($base.'organizations/{organization}/checkins', ['as' => 'checkins.create', 'uses' => 'CheckInController@create']);
    $api->get($base. 'organizations/{organization}/checkins/{checkin}', ['as' => 'checkins.show', 'uses' => 'CheckInController@find']);
    $api->put($base. 'organizations/{organization}/checkins/{checkin}', ['as' => 'checkins.update', 'uses' => 'CheckInController@update']);

    $api->get($base.'organizations/{organization}/checkins/{checkin}/messages', ['uses' => 'CheckInController@listMessages']);
    $api->get($base.'organizations/{organization}/checkins/{checkin}/recipients', ['uses' => 'CheckInController@listRecipients']);
    $api->post($base.'organizations/{organization}/checkins/{checkin}/recipients/{recipient}/messages', ['uses' => 'CheckInController@addMessage']);

    // check-in Replies
    $api->put($base.'organizations/{organization}/checkins/{checkin}/replies/{reply}', ['uses' => 'ReplyController@update']);
    $api->get($base.'organizations/{organization}/checkins/{checkin}/replies/{reply}', ['uses' => 'ReplyController@find']);
    $api->get($base.'organizations/{organization}/checkins/{checkin}/replies', ['uses' => 'ReplyController@listReplies']);
    $api->post($base.'organizations/{organization}/checkins/{checkin}/replies', ['uses' => 'ReplyController@addReply']);

    // Subscriptions
    $api->resource($base.'organizations/{organization}/subscriptions', 'SubscriptionController');
    $api->get($base.'organizations/{organization}/subscriptions/{subscription}/hostedpage/switchtopro', ['uses' => 'SubscriptionController@getProUpgradeHostedPageUrl']);
    $api->get($base.'organizations/{organization}/subscriptions/{subscription}/hostedpage/update', ['uses' => 'SubscriptionController@getUpdatePaymentInfoHostedPageUrl']);
    $api->post($base.'organizations/{organization}/subscriptions/{subscription}/credits', ['uses' => 'SubscriptionController@addCredits']);

    // emergency alerts
    $api->resource($base.'alerts', 'EmergencyAlertController');
    $api->get($base.'alerts/sources', ['uses' => 'EmergencyAlertController@sources']);    
});
