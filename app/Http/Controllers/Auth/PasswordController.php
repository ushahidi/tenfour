<?php

namespace TenFour\Http\Controllers\Auth;

use TenFour\Http\Controllers\Controller;
use TenFour\Models\User;
use TenFour\Contracts\Repositories\PersonRepository;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct(PersonRepository $people)
    {
        $this->people = $people;

        $this->middleware('guest');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postEmail(Request $request)
    {
        $this->validate($request, ['username' => 'required|email']);

        $user = $this->people->findByEmailAndSubdomain($request->input('username'), $request->input('subdomain'));

        if (!$user || !isset($user['person_type']) || $user['person_type'] == 'external') {
            return response('', 403);
        }

        $response = Password::sendResetLink($request->only('username', 'subdomain'), function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return response('ok', 200);

            default:
                return response('', 403);
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postReset(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'username' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $credentials = $request->only(
            'username', 'subdomain', 'password', 'password_confirmation', 'token'
        );

        $response = Password::reset($credentials, function ($user, $password) {
            $user->forceFill([
              'password' => $password,
            ])->save();
        });

        if ($response != Password::PASSWORD_RESET) {
            return response('', 403);
        }

        // this logic allows a user to accept invite by resetting password (#984)
        $member = $this->people->findByEmailAndSubdomain($request->input('username'), $request->input('subdomain'))->toArray();
        $member['person_type'] = 'user';
        $member['invite_token'] = null;
        $member = $this->people->update($member['organization_id'], $member, $member['id']);

        return response('ok', 200);
    }

}
