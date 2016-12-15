<?php

namespace RollCall\Http\Controllers;

use Log;
use Route;
use RollCall\Messaging\Storage\Reply as ReplyStorage;
use Illuminate\Http\Request;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

use Zend\Mail\Storage\Message as EmailMessage;

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
        if (config('rollcall.messaging.incoming_driver') == 'mailgun') {
            // Check if the request is authorized.
            if (hash_hmac('sha256', $request->input('timestamp').$request->input('token'), config('services.mailgun.secret')) !== $request->input('signature')) {
                return response('Rejected', 406);
            }

            $message = strip_tags($request->input('body-plain'));

            $this->reply_storage->save($request->input('from'), $message, null, 'mailgun');

            return response('Accepted', 200);
        }

        if (config('rollcall.messaging.incoming_driver') == 'aws-ses-sns') {
            // Instantiate the Message and Validator
            // @todo DI
            $message = Message::fromRawPostData();
            $validator = new MessageValidator();

            // Validate the message and log errors if invalid.
            try {
                $validator->validate($message);
            } catch (InvalidSnsMessageException $e) {
                Log::info('SNS Message Validation Error: ' . $e->getMessage());
                // Pretend we're not here if the message is invalid.
                abort(404);
            }

            // Check the type of the message and handle the subscription.
            if ($message['Type'] === 'SubscriptionConfirmation') {
                Log::info('SNS Subscription Confirmed');
                // Confirm the subscription by sending a GET request to the SubscribeURL
                file_get_contents($message['SubscribeURL']);
            }

            if ($message['Type'] === 'UnsubscribeConfirmation') {
                Log::info('SNS Unsubscribe');
                // Confirm the subscription by sending a GET request to the SubscribeURL
                file_get_contents($message['SubscribeURL']);
            }

            if ($message['Type'] === 'Notification') {
                $emailMessage = json_decode($message['Message'], true);
                $emailMessage = new EmailMessage(['raw' => $emailMessage['content']]);

                // Output first text/plain part
                $plainText = null;
                $html = null;
                foreach (new \RecursiveIteratorIterator($emailMessage) as $part) {
                    try {
                        if (strtok($part->contentType, ';') == 'text/plain') {
                            $plainText = $part;
                            break;
                        }
                        if (strtok($part->contentType, ';') == 'text/html') {
                            $html = $part;
                        }
                    } catch (\Zend\Mail\Exception $e) {
                        // ignore
                    }
                }

                $from = $emailMessage->getHeader('From')->getAddressList()->current()->getEmail();

                if ($plainText) {
                    Log::info("Received message: ". $message['MessageId']);
                    $this->reply_storage->save($from, $plainText->getContent(), $message['MessageId'], 'aws-ses-sns');
                }

                else {
                    Log::info("No plain text found for " . $message['MessageId'], $emailMessage['content']);
                }
            }
        }
    }
}
