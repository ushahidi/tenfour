<?php

namespace RollCall\Console\Commands;

use Illuminate\Console\Command;

use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Models\Contact;
use League\Csv\Reader;

class ImportContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacts:import {csv_file} {org=Ushahidi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import contacts into an existing organization';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $domain = 'rollcall.io';

        $csv = Reader::createFromPath($this->argument('csv_file'));

        // Assuming presence of header
        $rows = $csv->setOffset(1)->fetchAll();

        // Get organization
        $organization = Organization::firstOrCreate([
            'name' => $this->argument('org'),
        ]);

        $url = strtolower($this->argument('org')) . '.' .$domain;

        $organization->update([
            'url'  => $url,
        ]);

        /* Expected field indexes:
           1 => "<First Name>"
           2 => "<Last Name>"
           6 => "<email>"
           7 => "<phone>"
        */

        $ids = [];

        foreach ($rows as $row)
        {
            $name = $row[1] . ' ' . $row[2];
            $email = $row[6];
            $phone_number = $row[7];

            $member = User::firstOrCreate([
                'email'    => $email,
                'name'     => $name,
            ]);

            // Add email contact
            Contact::create([
                'user_id'     => $member['id'],
                'type'        => 'email',
                'can_receive' => 1,
                'contact'     => $email,
            ]);

            // Add phone contact
            Contact::create([
                'user_id'     => $member['id'],
                'type'        => 'phone',
                'can_receive' => 1,
                'contact'     => $phone_number,
            ]);

            $ids[$member['id']] = ['role' => 'member'];
        }

        $organization->members()->sync($ids, false);
    }
}
