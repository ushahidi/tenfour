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
    $api->get($base.'users', ['as' => 'users.index', 'uses' => 'UsersController@show']);
    $api->post($base.'users', ['uses' => 'UsersController@create']);
});

app('Dingo\Api\Routing\UrlGenerator')->version('v1')->route('users.index');
