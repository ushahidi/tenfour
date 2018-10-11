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

Route::get('health', 'HealthController@shallow');
Route::get('health/deep', 'HealthController@deep');

// Receive push emails
Route::post('mail/receive', 'MailController@receive');

Route::post('password/email', ['uses' => 'Auth\PasswordController@postEmail']);
Route::post('password/reset', ['uses' => 'Auth\PasswordController@postReset']);

// Receive push MO SMS
Route::post('sms/receive/africastalking', 'SMSController@receiveAfricasTalking');
Route::match(['get', 'post'], 'sms/receive/nexmo', 'SMSController@receiveNexmo');

Route::get('voice/answer', 'VoiceController@makeCheckInNCCO');
Route::post('voice/event', 'VoiceController@handleEvent');
Route::post('voice/reply', 'VoiceController@handleReply');


Route::post('invite/{organization}/accept/{member}', ['uses' => 'Api\First\OrganizationController@acceptInvite']);

// Unsubscribe from emails
Route::post('unsubscribe', ['uses' => 'Api\First\PersonContactController@unsubscribe']);

//Get UserAvatars
Route::get('useravatar/{filename}',['uses' => 'UseravatarController@show']);

// SES bounces and complaints
Route::post('ses/bounces', 'SESBounceController@handleBounce');
Route::post('ses/complaints', 'SESBounceController@handleComplaint');

// Get/update checkin with a token when I am not logged in
Route::get('checkins/{checkin}', ['uses' => 'Api\First\CheckInController@findById']);
Route::post('checkins/{checkin}/replies', ['uses' => 'Api\First\ReplyController@addReplyFromToken']);

// Address verification
Route::post('verification/email', 'VerificationController@sendEmailVerification');
Route::get('verification/email', 'VerificationController@verifyEmail');

// ChargeBee Webhooks
Route::post('/chargebee/webhook', 'ChargeBeeWebhookController@handle')->middleware('auth.basic.chargebee-webhook');

// Create organization needs to be outside api because it uses client_credentials grants
Route::post('organization/create', ['middleware' => 'client_credentials', 'uses' => 'Api\First\OrganizationController@store']);
Route::post('organization/lookup', ['uses' => 'Api\First\OrganizationController@lookup']);
