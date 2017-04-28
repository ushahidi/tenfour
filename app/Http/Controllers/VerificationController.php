<?php

namespace RollCall\Http\Controllers;

use RollCall\Http\Requests\EmailVerificationRequest;
use RollCall\Contracts\Repositories\UnverifiedAddressRepository;
use RollCall\Jobs\SendVerificationEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(UnverifiedAddressRepository $addresses)
    {
        $this->addresses = $addresses;
    }

    public function sendEmailVerification(EmailVerificationRequest $request)
    {
        $token = Hash::Make(config('app.key'));

        $payload = array_only($request->all(), ['address']) + [
            'verification_token' => $token
        ];
        $address = $this->addresses->create($payload);

        dispatch(new SendVerificationEmail($payload));

        return $address;
    }

    public function verifyEmail(Request $request)
    {
        $address = $this->addresses->getByAddress($request['address'], $request['token']);

        if (! $address) {
            abort(404);
        }

        // Return valid address and delete it
        return $this->addresses->delete($address['id']);
    }
}
