<?php

namespace RollCall\Messaging;

use RollCall\Contracts\Messaging\MessageServiceFactory as MessageServiceFactoryInterface;

class MessageServiceFactory implements messageServiceFactoryInterface
{
    public function make($message_type)
    {
        $message_service = null;

        if ($message_type === 'email') {
            $message_service = new MailService;
        }

        if ($message_type === 'phone') {
            $message_service = new SMSService;
        }

        if ($message_type === 'slack') {
            $message_service = new SlackService;
        }

        return $message_service;
    }
}
