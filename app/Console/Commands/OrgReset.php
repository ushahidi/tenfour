<?php

namespace RollCall\Console\Commands;

use Illuminate\Console\Command;
use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Contracts\Repositories\PersonRepository;
use Illuminate\Support\Facades\Password;

class OrgReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'org:reset {subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset a user passwords for entire org';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PersonRepository $people, OrganizationRepository $organizations)
    {
        $org = $organizations->findBySubdomain($this->argument('subdomain'));

        $sent = 0;


        if ($this->confirm("Are you sure you want to reset the password for all users in {$org['name']}")) {
            foreach ($people->all($org['id']) as $person) {
                $email = false;
                foreach($person['contacts'] as $contact)
                {
                  if ($contact['type'] == 'email') {
                    $email = $contact['contact'];
                  }
                }

                if ($email) {
                    Password::sendResetLink(['username' => $email], function (Message $message) {
                        $message->subject($this->getEmailSubject());
                    });
                    $sent++;
                }
            }

            $this->info("Sent reset emails to {$sent} users");
        }
    }
}
