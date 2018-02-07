<?php

namespace TenFour\Http\Controllers;

use Log;
use Route;
use TenFour\Contracts\Repositories\ReplyRepository;
use Illuminate\Http\Request;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

use Zend\Mail\Storage\Message as EmailMessage;
use EmailReplyParser\Parser\EmailParser;

class MailController extends Controller
{
    /**
     * @var TenFour\Messaging\Storage\Replies
     */
    protected $replies;

    public function __construct(ReplyRepository $replies)
    {
        $this->replies = $replies;
    }

    /**
     * Receive push email requests. Currently only supports Mailgun requests.
     *
     * @param Request $request
     * @return Response
     */
    public function receive(Request $request)
    {
        if (config('tenfour.messaging.incoming_driver') == 'mailgun') {
            return $this->receiveMailgun($request);
        }

        if (config('tenfour.messaging.incoming_driver') == 'aws-ses-sns') {
            return $this->receiveAWS($request);
        }
    }

    protected function saveEmail($from, $message, $to, $message_id, $provider) {
        $visibleMessage = \EmailReplyParser\EmailReplyParser::parseReply($message);

        $check_in_id = null;
        if (preg_match('/^checkin-(\d*)@.*$/', $to, $matches)) {
            $check_in_id = $matches[1];
        }

        $this->replies->save($from, $visibleMessage, $message_id, $check_in_id, $provider);
    }

    protected function receiveMailgun() {
        // Check if the request is authorized.
        if (hash_hmac('sha256', $request->input('timestamp').$request->input('token'), config('services.mailgun.secret')) !== $request->input('signature')) {
            return response('Rejected', 406);
        }

        $message = strip_tags($request->input('body-plain'));

        $this->saveEmail($request->input('from'), $message, $request->input('to'), null, 'mailgun');

        return response('Accepted', 200);
    }

    protected function receiveAWS() {
            // Instantiate the Message and Validator
            // @todo DI
            $message = Message::fromRawPostData();

            if (config('tenfour.messaging.validate_sns_message')) {
                // Validate the message and log errors if invalid.
                $validator = new MessageValidator();

                try {
                    $validator->validate($message);
                } catch (InvalidSnsMessageException $e) {
                    Log::info('SNS Message Validation Error: ' . $e->getMessage());
                    // Pretend we're not here if the message is invalid.
                    abort(404);
                }
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
                $original_content = $emailMessage['content'];
                $emailMessage = new EmailMessage(['raw' => $original_content]);

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
                $to   = $emailMessage->getHeader('To')->getAddressList()->current()->getEmail();

                if ($plainText) {
                    Log::info("Received message: ". $message['MessageId']);
                    $this->saveEmail($from, $plainText->getContent(), $to, $message['MessageId'], 'aws-ses-sns');
                }
                elseif ($html) {
                    Log::info("Received message: ". $message['MessageId']);
                    $text = strip_tags($html->getContent());
                    $this->saveEmail($from, $text, $to, $message['MessageId'], 'aws-ses-sns');
                }
                else {
                    Log::info("No plain text or html found for " . $message['MessageId'], ['original_content' => $original_content]);
                }
            }
    }
}
