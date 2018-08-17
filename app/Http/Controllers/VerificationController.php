<?php

namespace TenFour\Http\Controllers;

use TenFour\Http\Requests\EmailVerificationRequest;
use TenFour\Contracts\Repositories\UnverifiedAddressRepository;
use TenFour\Jobs\SendVerificationEmail;
use TenFour\Models\UnverifiedAddress;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    const DIGITS_IN_VERIFICATION_CODE = 6;
    const MAX_CODE_ATTEMPTS = 3;

    public function __construct(UnverifiedAddressRepository $addresses)
    {
        $this->addresses = $addresses;
    }

    private function makeVerificationCode()
    {
        return str_pad(rand(0, pow(10, self::DIGITS_IN_VERIFICATION_CODE)-1), self::DIGITS_IN_VERIFICATION_CODE, '0', STR_PAD_LEFT);
    }

    public function sendEmailVerification(EmailVerificationRequest $request)
    {
        $address = $this->addresses->getByAddress($request['address']);

        if (!$address) {
          $token = Hash::Make(config('app.key'));
        $code = $this->makeVerificationCode();

          $payload = array_only($request->all(), ['address']) + [
            'verification_token' => $token,
            'code' => $code
          ];
          $address = $this->addresses->create($payload);
        } else {
          abort(409);
        }

      dispatch((new SendVerificationEmail($payload))/*->no Queue('mails')*/);

        return $address;
    }

    public function verifyEmail(Request $request)
    {
        if (isset($request['token'])) {
            $address = $this->addresses->getByAddress($request['address'], $request['token'], null);
        } elseif (isset($request['code'])) {
            $address = $this->addresses->getByAddress($request['address'], null, $request['code']);
        }

        if (!$address || $address['code_attempts'] >= self::MAX_CODE_ATTEMPTS) {
            $this->addresses->incrementCodeAttempts($request['address']);
            abort(404);
        }

        // Return valid address and delete it
        // return $this->addresses->delete($address['id']);

        return $address;
    }
}
