<?php

namespace RollCall\Messaging\Storage;

use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\ReplyRepository;
use RollCall\Messaging\AnswerParser;

class Reply
{
    public function __construct(RollcallRepository $roll_calls, ContactRepository $contacts, ReplyRepository $replies)
    {
        $this->roll_calls = $roll_calls;
        $this->contacts = $contacts;
        $this->replies = $replies;
    }

    public function save($from, $message, $message_id = 0, $provider = null)
    {
        $contact = $this->contacts->getByContact($from);

        if ($contact) {

            // Get last roll call id that was sent to the the the contact
            $roll_call_id = $this->roll_calls->getLastSentMessageId($contact['id']);

            // Add reply if roll call exists
            if ($roll_call_id) {

                $roll_call = $this->roll_calls->find($roll_call_id);

                $answer = AnswerParser::parse($message, $roll_call['answers']);

                $input = [
                    'message'      => $message,
                    'user_id'      => $contact['user']['id'],
                    'roll_call_id' => $roll_call_id,
                    'contact_id'   => $contact['id'],
                    'answer'       => $answer,
                    'message_id'   => $message_id,
                ];

                $this->replies->create($input);

                // Update response status
                $this->roll_calls->updateRecipientStatus($roll_call_id, $contact['user']['id'], 'replied');
            }
        }
    }

    public function getLastReplyId($provider = null)
    {
        // TODO: Track this by provider
        return $this->replies->getLastReplyId();
    }
}
