<?php

namespace RollCall\Http\Controllers;

use Aws\Sns\Message;
use markdunphy\SesSnsTypes\Notification\BounceMessage;
use markdunphy\SesSnsTypes\Notification\ComplaintMessage;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\RollCallRepository;
use Log;

class SESBounceController extends Controller
{
    use ValidatesMessages;

    public function __construct(ContactRepository $contacts, RollCallRepository $roll_calls)
    {
        $this->contacts = $contacts;
        $this->roll_calls = $roll_calls;
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

        if (config('rollcall.messaging.validate_sns_message')) {
            $this->validateSnsMessageOrAbort($message);
        }

        $bounce = new BounceMessage($message['Message']);

        $bounce_threshold = config('rollcall.messaging.bounce_threshold');

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

        if (config('rollcall.messaging.validate_sns_message')) {
            $this->validateSnsMessageOrAbort($message);
        }

        $complaint = new ComplaintMessage($message['Message']);

        $recipients = $complaint->getComplainedRecipients();

        $complaint_threshold = config('rollcall.messaging.complaint_threshold');

        foreach ($recipients as $recipient)
        {
            $complaint_type = $complaint->getComplaintFeedbackType();

            Log::info('Received complaint of type '. $complaint_type . ' from '. $recipient->getEmailAddress());

            // Get contact id
            $contact = $this->contacts->getByContact($recipient->getEmailAddress());

            $roll_call_id = $this->roll_calls->getLastSentMessageId($contact['id']);

            if ($roll_call_id) {
                $roll_call = $this->roll_calls->find($roll_call_id);

                if ($roll_call['complaint_count'] < $complaint_threshold) {
                    $new_count = $roll_call['complaint_count'] + 1;
                    $this->roll_calls->setComplaintCount($new_count, $roll_call_id);
                }
            }
        }
    }
}
