<?php

namespace RollCall\Console\Commands;

use Illuminate\Console\Command;

use RollCall\Messaging\SMSService;
use RollCall\Messaging\Storage\Reply as ReplyStorage;

class ReceiveSMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:receive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets a list of pending SMS from the default provider';

    /**
     * Service used to get messages using configured provider
     *
     * @var RollCall\Contracts\Messaging\MessageService
     */
    protected $message_service;

    /**
     * The storage instance.
     * @var RollCall\Messaging\Storage\Reply
     */
    protected $reply_storage;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SMSService $message_service, ReplyStorage $reply_storage)
    {
        $this->message_service = $message_service;
        $this->reply_storage = $reply_storage;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get last reply id from provider
        // TODO: Track provider ids separately. Also this is most likely provider
        // dependent
        $last_reply_id = $this->reply_storage->getLastReplyId();

        $messages = $this->message_service->getMessages(['lastReceivedId' => $last_reply_id]);

        foreach ($messages as $message)
        {
            $saved = $this->reply_storage->save($message['from'], $message['message'], $message['id']);

            if ($saved) {
                $this->sendResponseReceivedSMS($message['from']);
            }
        }
    }

    protected function sendResponseReceivedSMS($to) {
        $message_service->setView('sms.response_received');
        $message_service->send($to);
    }
}
