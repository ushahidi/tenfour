<?php

use Dingo\Api\Routing\Router;

$api = app(Router::class);
$api->version('v1', [
	'namespace' => 'RollCall\Http\Controllers\Api\First',
	'protected' => true,
], function ($api) {
	// Authentication
	//////////////////////////////////////////////////////////////////////
	$api->post('auth', ['protected' => false, 'uses' => 'AuthController@token']);
});
