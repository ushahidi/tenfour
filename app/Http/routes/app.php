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
    Validator::make(Input::all(), [
        'username'     => 'required_if:grant_type,password|email',
        'password'     => 'required_if:grant_type,password',
        'organization' => 'required_if:grant_type,password',
    ])->validate();

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

Route::post('invite/{organization}/accept/{member}', ['uses' => 'Api\First\OrganizationController@acceptInvite']);

// Unsubscribe from emails
Route::post('unsubscribe', ['uses' => 'Api\First\PersonContactController@unsubscribe']);

//Get UserAvatars
Route::get('useravatar/{filename}',['uses' => 'UseravatarController@show']);

// SES bounces and complaints
Route::post('ses/bounces', 'SESBounceController@handleBounce');
Route::post('ses/complaints', 'SESBounceController@handleComplaint');

// Get/update RollCall with a token when I am not logged in
Route::get('rollcalls/{rollcall}', ['uses' => 'Api\First\RollCallController@find']);
Route::post('rollcalls/{rollcall}/replies', ['uses' => 'Api\First\ReplyController@addReplyFromToken']);

// Address verification
Route::post('verification/email', 'VerificationController@sendEmailVerification');
Route::get('verification/email', 'VerificationController@verifyEmail');

// ChargeBee Webhooks
Route::post('/chargebee/webhook', 'ChargeBeeWebhookController@handle')->middleware('auth.basic.chargebee-webhook');
