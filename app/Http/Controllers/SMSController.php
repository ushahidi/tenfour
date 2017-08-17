<?php

namespace RollCall\Http\Controllers;

use Log;
use App;
use RollCall\Messaging\Storage\Reply as ReplyStorage;
use RollCall\Messaging\SMSService;
use RollCall\Messaging\Validators\NexmoMessageValidator;
use Illuminate\Http\Request;
use SMS;
use libphonenumber\NumberParseException;

class SMSController extends Controller
{
    /**
     * @var RollCall\Messaging\Storage\Replies
     */
    protected $reply_storage;

    public function __construct(ReplyStorage $reply_storage, SMSService $message_service)
    {
        $this->reply_storage = $reply_storage;
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

        Log::info("[SMSController] Received SMS message from " . $incoming->from() . " with id: " . $incoming->id());

        $from = $incoming->from();

        if (!starts_with($from, '+')) {
            $from = '+' . $from;
        }

        $saved = $this->reply_storage->save(
            $from,
            $incoming->message(),
            $incoming->id()
        );

        if ($saved) {
            try {
                $from = App::make('RollCall\Messaging\PhoneNumberAdapter', [$from]);
                $this->message_service->sendResponseReceivedSMS($from);
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
            return response('Hi', 200);
        }

        return $this->receive('nexmo');
    }

}
