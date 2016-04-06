<?php

use Dingo\Api\Routing\Router;

$version = 'v1';
$base = 'api/'.$version.'/';
$api = app(Router::class);

$api->version($version, [
	'namespace' => 'RollCall\Http\Controllers\Api\First',
	'protected' => true,
	'middleware' => 'api.auth',
	'scopes' => ['user'],
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
});	
