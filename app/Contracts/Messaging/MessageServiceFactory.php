<?php

namespace TenFour\Contracts\Messaging;

interface MessageServiceFactory
{
    /**
     * Create a new Sender based on type of message
     *
     * @param string $message_type
     * @return TenFour\Contracts\Messaging\Sender
     */
    public function make($message_type);

}
