<?php
namespace RollCall\Messaging\Drivers;

use GuzzleHttp\Client;
use SimpleSoftwareIO\SMS\Drivers\DriverInterface;
use SimpleSoftwareIO\SMS\Drivers\AbstractSMS;
use SimpleSoftwareIO\SMS\MakesRequests;
use SimpleSoftwareIO\SMS\OutgoingMessage;

class AfricasTalking extends AbstractSMS implements DriverInterface
{
    /**
     * The API key.
     *
     * @var string
     */
    protected $api_key;

    /**
     * The API url for RESTful requests.
     *
     * @var string
     */
    protected $api_rest_url = 'https://api.africastalking.com/version1/messaging';

    /**
     * The API url.
     *
     * @var string
     */
    protected $username;

    /**
     * Create the driver instance.
     *
     * @param Client $client The Guzzle Client
     */
    public function __construct(Client $client, $api_key, $username)
    {
        $this->client = $client;
        $this->api_key = $api_key;
        $this->username = $username;
    }

    /**
     * Sends a SMS message.
     *
     * @param \SimpleSoftwareIO\SMS\OutgoingMessage $message
     */
    public function send(OutgoingMessage $message)
    {
        $numbers = implode(',', $message->getTo());

        $headers = [
            'Accept' => 'application/json',
            'Apikey' => $this->api_key,
        ];

        $body = [
            'from'     => $message->getFrom(),
            'username' => $this->username,
            'message'  => $message->composeMessage(),
            'to'       => $numbers
        ];

        return $this->client->request('POST', $this->api_rest_url, [
            'headers'     => $headers,
            'form_params' => $body,
        ]);
    }

    public function checkMessages(array $options = [])
    {
        $query = [
            'username' => $this->username
        ] + $options;

        $headers = [
            'Accept' => 'application/json',
            'Apikey' => $this->api_key,
        ];

        $raw_messages = (string) $this->client->request('GET', $this->api_rest_url, [
            'headers' => $headers,
            'query'   => $query,
        ])->getBody();

        $raw_messages = json_decode($raw_messages, true);

        return $this->makeMessages($raw_messages['SMSMessageData']['Messages']);
    }


    public function getMessage($message_id)
    {
        //
    }

    public function receive($raw)
    {
        //
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
