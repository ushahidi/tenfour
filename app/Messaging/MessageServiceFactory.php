<?php

namespace TenFour\Messaging;

use TenFour\Contracts\Messaging\MessageServiceFactory as MessageServiceFactoryInterface;

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

        if ($message_type === 'fcm') {
            $message_service = new FCMService;
        }

        return $message_service;
    }
}
