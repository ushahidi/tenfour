<?php
namespace TenFour\Messaging\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

use SimpleSoftwareIO\SMS\Drivers\DriverInterface;
use SimpleSoftwareIO\SMS\Drivers\AbstractSMS;
use SimpleSoftwareIO\SMS\MakesRequests;
use SimpleSoftwareIO\SMS\OutgoingMessage;

class BulkSMS extends AbstractSMS implements DriverInterface
{
    /**
     * The token id.
     *
     * @var string
     */
    protected $token_id;

    /**
     * The token secret.
     *
     * @var string
     */
    protected $token_secret;

    /**
     * The API url for RESTful requests.
     *
     * @var string
     */
    protected $api_rest_url = 'https://api.bulksms.com/v1/messages';

    /**
     * Create the driver instance.
     *
     * @param Client $client The Guzzle Client
     */
    public function __construct(Client $client, $token_id, $token_secret)
    {
        $this->client = $client;
        $this->token_id = $token_id;
        $this->token_secret = $token_secret;
    }

    /**
     * Sends a SMS message.
     *
     * @param \SimpleSoftwareIO\SMS\OutgoingMessage $message
     */
    public function send(OutgoingMessage $message)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->token_id.':'.$this->token_secret),
        ];

        $body = [
            // 'from'     => $message->getFrom(),
            'from'     => [ 'type' => 'REPLIABLE' ],
            'body'     => $message->composeMessage(),
            'to'       => $message->getTo()[0],
            'encoding' => 'UNICODE',
        ];

        return $this->client->request('POST', $this->api_rest_url, [
            'headers'     => $headers,
            'body'     => json_encode($body),
        ]);
    }

    public function checkMessages(array $options = [])
    {
        // Not implemented - we're using BulkSMS MO URL callback
    }


    public function getMessage($message_id)
    {
        // Not implemented - we're using BulkSMS MO URL callback
    }

    public function receive($raw)
    {
        $message = $this->createIncomingMessage();
        $message->setRaw($raw->get());
        $message->setMessage($raw->get('message'));
        $message->setFrom($raw->get('sender'));
        $message->setId($raw->get('msg_id'));
        // $message->setTo($raw->get('to'));

        return $message;
    }

    public function processReceive($raw_message)
    {
        $message = $this->createIncomingMessage();
        $message->setRaw($raw_message);
        $message->setFrom($raw_message['from']);
        $message->setTo($raw_message['to']);
        $message->setMessage($raw_message['text']);
        $message->setId($raw_message['id']);

        return $message;
    }
}
