<?php

namespace RollCall\Http\Controllers\Auth;

use RollCall\Http\Controllers\Controller;
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
    public function __construct()
    {
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

        $response = Password::sendResetLink($request->only('username'), function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return response([$response], 200);

            default:
                return response([$response], 403);
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
            'username', 'password', 'password_confirmation', 'token'
        );

        $response = Password::reset($credentials, function ($user, $password) {
            $user->forceFill([
              'password' => $password,
            ])->save();
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                return response([$response], 200);

            default:
                return response([$response], 403);
        }
    }

}
