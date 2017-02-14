<?php

namespace RollCall\Messaging\Validators;

use Illuminate\Http\Request;
use RollCall\Messaging\InvalidMOMessageException;

class NexmoMessageValidator
{

    /**
     * @var string Security secret used to sign requests
     */
    private $secret;

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     *  Validate request containing message from Nexmo
     *
     * @param Request $request
     *
     * @throws InvalidMOMessageException If the request signature
     *                                   is invalid.
     */
    public function validate(Request $request)
    {
        $request = $request->all();

        if (! isset($request['sig'])) {
            throw new InvalidMOMessageException('Message is unsigned');
        }

        // Get the current timestamp as UTC + 0 timestamp for comparison
        $now = time() - date('Z');
        $message_timestamp = strtotime($request['message-timestamp']);

        $difference = abs($now - $message_timestamp);

        //Message cannot be more than 5 minutes old
        $max_delta = 5 * 60;
        if ($difference > $max_delta) {
            throw new InvalidMOMessageException('Message is too old');
        }

        //Store the signature locally and remove it from $request
        $message_signature = $request['sig'];
        unset($request['sig']);

        // Sort the parameters so they are in alphabetic order
        ksort($request);

        $generated_signature = md5('&' .urldecode(http_build_query($request)) .$this->secret);

        if (! hash_equals($message_signature, $generated_signature)) {
            throw new InvalidMOMessageException('Invalid signature');
        }
    }

    /**
     *  Checks whether request from Nexmo is valid
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isValid(Request $request)
    {
        try {
            $this->validate($request);
        }
        catch (InvalidMOMessageException $e) {
            return false;
        }

        return true;
    }
}
