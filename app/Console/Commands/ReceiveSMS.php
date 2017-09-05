<?php

namespace RollCall\Console\Commands;

use Illuminate\Console\Command;

use Log;
use RollCall\Messaging\SMSService;
use RollCall\Contracts\Repositories\ReplyRepository;

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
     * @var RollCall\Contracts\Repositories\ReplyRepository
     */
    protected $replies;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SMSService $message_service, ReplyRepository $replies)
    {
        $this->message_service = $message_service;
        $this->replies = $replies;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::warning('Disabled sms:receive command as it is unused and needs maintenance.');
        return;

        // Get last reply id from provider
        // TODO: Track provider ids separately. Also this is most likely provider
        // dependent
        $last_reply_id = $this->replies->getLastReplyId();

        $messages = $this->message_service->getMessages(['lastReceivedId' => $last_reply_id]);

        foreach ($messages as $message)
        {
            Log::info('[ReceiveSMS] Received SMS message from: ' . $message['from'] . " with id: " . $message['id']);

            if (!starts_with($message['from'], '+')) {
                $message['from'] = '+' . $message['from'];
            }

            $saved = $this->replies->save($from, $message['message'], $message['id']);

            // TODO:
            // send "Response received" from same number it was received on
            // log sms with relevant rollcall_id

            if ($saved) {
                $this->message_service->sendResponseReceivedSMS($message['from']);
            }
        }
    }

}
