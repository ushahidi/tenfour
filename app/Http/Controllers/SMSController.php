<?php

namespace RollCall\Http\Controllers;

use Log;
use RollCall\Messaging\Storage\Reply as ReplyStorage;
use RollCall\Messaging\Validators\NexmoMessageValidator;
use Illuminate\Http\Request;
use SMS;

class SMSController extends Controller
{
    /**
     * @var RollCall\Messaging\Storage\Replies
     */
    protected $reply_storage;

    public function __construct(ReplyStorage $reply_storage)
    {
        $this->reply_storage = $reply_storage;
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

        Log::info("Received SMS message: ". $incoming->id());

        $this->reply_storage->save(
            $incoming->from(),
            $incoming->message(),
            $incoming->id()
        );

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
            abort(404);
        }

        return $this->receive('nexmo');
    }

}
