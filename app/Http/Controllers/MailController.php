<?php

namespace RollCall\Http\Controllers;

use RollCall\Messaging\Storage\Reply as ReplyStorage;
use Illuminate\Http\Request;

class MailController extends Controller
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
     * Receive push email requests. Currently only supports Mailgun requests.
     *
     * @param Request $request
     * @return Response
     */
    public function receive(Request $request)
    {
        // Check if the request is authorized.
        if (hash_hmac('sha256', $request->input('timestamp').$request->input('token'), config('mailgun.secret')) !== $request->input('signature')) {
            return response('Rejected', 406);
        }

        $message = strip_tags($request->input('body-plain'));

        $this->reply_storage->save($request->input('from'), $message);

        return response('Accepted', 200);
    }
}
