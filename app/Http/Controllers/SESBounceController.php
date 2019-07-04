<?php

namespace TenFour\Http\Controllers;

use Aws\Sns\Message;
use markdunphy\SesSnsTypes\Notification\BounceMessage;
use markdunphy\SesSnsTypes\Notification\ComplaintMessage;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Contracts\Repositories\PersonRepository;
use Log;
use Illuminate\Support\Facades\Notification;
use TenFour\Notifications\Complaint;
use TenFour\Models\Contact;
use TenFour\Models\CheckIn;

class SESBounceController extends Controller
{
    use ValidatesMessages;

    public function __construct(ContactRepository $contacts, CheckInRepository $check_ins, PersonRepository $people)
    {
        $this->contacts = $contacts;
        $this->check_ins = $check_ins;
        $this->people = $people;
    }

    /**
     * Handle SES bounce
     *
     * @param Request $request
     * @return Response
     */
    public function handleBounce()
    {
        $message = Message::fromRawPostData();

        // Log::debug((array) $message);

        if (config('tenfour.messaging.validate_sns_message')) {
            $this->validateSnsMessageOrAbort($message);
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

        $bounce = new BounceMessage(json_decode($message['Message'], true));

        $bounce_threshold = config('tenfour.messaging.bounce_threshold');

        $recipients = $bounce->getBouncedRecipients();

        foreach ($recipients as $recipient)
        {
            Log::info('Received bounce from '. $recipient->getEmailAddress());

            $count = 0;

            // Get contact id
            $contact = $this->contacts->getByContact($recipient->getEmailAddress());

            if (! $contact || $contact['bounce_count'] == $bounce_threshold) {
                continue;
            }

            // Set to threshold maximum if we have a hard bounce
            if ($bounce->isHardBounce()) {
                $count  = $bounce_threshold;
            }
            // or else increment bounce count by 1 except for out-of-office bounces
            elseif ($bounce->isSoftBounce() && $bounce->getBounceSubType() !== 'General') {
                $count = $contact['bounce_count'] + 1;
            }

            $this->contacts->setBounceCount($count, $contact['id']);
        }
    }

    /**
     * Handle SES complaint
     *
     * @param Request $request
     * @return Response
     */
    public function handleComplaint()
    {
        $message = Message::fromRawPostData();

        Log::debug((array) $message);

        if (config('tenfour.messaging.validate_sns_message')) {
            $this->validateSnsMessageOrAbort($message);
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

        $complaint = new ComplaintMessage(json_decode($message['Message'], true));

        $recipients = $complaint->getComplainedRecipients();

        $complaint_threshold = config('tenfour.messaging.complaint_threshold');

        foreach ($recipients as $recipient)
        {
            $complaint_type = $complaint->getComplaintFeedbackType();

            Log::info('Received complaint of type '. $complaint_type . ' from '. $recipient->getEmailAddress());

            // Get contact id
            $contact = $this->contacts->getByContact($recipient->getEmailAddress());

            $check_in_id = $this->check_ins->getLastSentMessageId($contact['id']);

            if ($check_in_id) {
                $check_in = $this->check_ins->find($check_in_id);

                if ($check_in['complaint_count'] < $complaint_threshold) {
                    $new_count = $check_in['complaint_count'] + 1;
                    $this->check_ins->setComplaintCount($new_count, $check_in_id);
                }

                $check_in_obj = CheckIn::where('id', $check_in_id)->first();
            }

            $contact_obj = Contact::where('id', $contact['id'])->first();

            Notification::send($this->people->getAdmins($contact_obj->user->organization->id), new Complaint($contact_obj->user, $check_in_obj));
        }
    }
}
