<?php

namespace TenFour\Http\Controllers;

use Log;
use App;
use TenFour\Contracts\Repositories\ReplyRepository;
use TenFour\Messaging\SMSService;
use TenFour\Messaging\Validators\NexmoMessageValidator;
use Illuminate\Http\Request;
use SMS;
use libphonenumber\NumberParseException;

class SMSController extends Controller
{
    /**
     * @var TenFour\Messaging\Storage\Replies
     */
    protected $reply_storage;

    public function __construct(ReplyRepository $replies, SMSService $message_service)
    {
        $this->replies = $replies;
        $this->message_service = $message_service;
    }

    /**
     * Save MOs using appropriate driver
     *
     * @param $driver
     *
     * @return Response
     */
    protected function receive($driver)
    {
        SMS::driver($driver);

        $incoming = SMS::receive();

        $from = $incoming->from();
        $to = $incoming->to();

        Log::info("[SMSController:".$driver."] Received SMS message to=" . $to . " from=" . $from . " with id=" . $incoming->id());

        if (!starts_with($from, '+')) {
            $from = '+' . $from;
        }

        $reply_obj = $this->replies->save(
            $from,
            $incoming->message(),
            $incoming->id(),
            null,
            null,
            $to
        );

        if ($reply_obj) {
            $response_from = $reply_obj['from'];
            $check_in_id = $reply_obj['check_in_id'];

            try {
                $response_to = App::make('TenFour\Messaging\PhoneNumberAdapter');
                $response_to->setRawNumber($from);
                $this->message_service->sendResponseReceivedSMS($response_to, $response_from, $check_in_id);
            } catch (NumberParseException $exception) {
                // Somehow the number format could not be parsed
                Log::info("[SMSController] Could not parse MSISDN: " . $from);
            }
        }

        return response('Accepted', 200);
    }

    /**
     * Receive push MOs from Africa's talking
     *
     * @param Request $request
     * @return Response
     */
    public function receiveAfricasTalking()
    {
        return $this->receive('africastalking');
    }

    /**
     * Receive push MOs from Nexmo
     *
     * @param Request $request
     * @return Response
     */
    public function receiveNexmo(Request $request, NexmoMessageValidator $validator)
    {
        if (! $validator->isValid($request)) {
            Log::error('Received an invalid request from nexmo');
            return response('Hi', 200);
        }

        return $this->receive('nexmo');
    }

}
