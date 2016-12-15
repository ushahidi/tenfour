<?php

namespace RollCall\Contracts\Messaging;

interface MessageService
{
    /* Send a message to a destination or list of destination
     *
     * @param string|array $to
     * @param string $message
     * @param array $additional_params
     */
    public function send($to, $message, $additional_params = []);

    /**
     * Gets Messages from a server
     *
     * @param array $options
     *
     * @return array
     */
    public function getMessages(array $options = []);
}
