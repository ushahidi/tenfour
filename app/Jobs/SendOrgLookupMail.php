<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use TenFour\Contracts\Messaging\MessageServiceFactory;
use TenFour\Mail\OrgLookup as OrgLookupMail;

use Log;

class SendOrgLookupMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $organizations;
    protected $email;

    /**
     * Create a new job instance.
     *
     * @param Array $organizations
     * @param Array $email
     *
     * @return void
     */
    public function __construct(Array $organizations, $email)
    {
        $this->organizations = $organizations;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MessageServiceFactory $message_service_factory)
    {
        $message_service = $message_service_factory->make('email');
        $message_service->send(
            $this->email,
            new OrgLookupMail($this->organizations),
            ['type' => 'orglookup']
        );
    }
}
