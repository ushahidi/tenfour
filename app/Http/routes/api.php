<?php

use Dingo\Api\Routing\Router;

$api = app(Router::class);
$api->version('v1', [
	'namespace' => 'RollCall\Http\Controllers\Api\First',
	'protected' => true,
	'middleware' => 'api.auth',
], function ($api) {
    	
	//Users 
    	////////////////////////////////////////////////////////////////////
    	$api->get('users', ['uses' => 'UsersController@show']);
    	$api->post('users', ['uses' => 'UsersController@create']);
});
