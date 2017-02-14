<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// @todo change this to a list of endpoints
Route::get('/', function () {
	return view('welcome');
});

Route::post('oauth/access_token', function() {
    return Response::json(Authorizer::issueAccessToken());
});

Route::get('health', 'HealthController@shallow');
Route::get('health/deep', 'HealthController@deep');

// Receive push emails
Route::post('mail/receive', 'MailController@receive');

Route::post('password/email', ['uses' => 'Auth\PasswordController@postEmail']);
Route::post('password/reset', ['uses' => 'Auth\PasswordController@postReset']);

// Receive push MO SMS
Route::post('sms/receive/africastalking', 'SMSController@receiveAfricasTalking');
Route::match(['get', 'post'], 'sms/receive/nexmo', 'SMSController@receiveNexmo');

Route::post('invite/{organization}/accept/{member}', ['uses' => 'Api\First\PersonController@acceptInvite']);

Route::post('unsubscribe', ['uses' => 'Api\First\PersonContactController@unsubscribe']);
