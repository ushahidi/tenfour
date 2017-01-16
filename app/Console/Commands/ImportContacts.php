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

        $headers = $csv->fetchOne();

        if (count(array_diff(['First Name', 'Last Name', 'Email Address', 'Phone Number'], $headers)) > 0) {
            $this->error("CSV must have the following columns: 'First Name', 'Last Name', 'Email Address', 'Phone Number'");
            return;
        }

        // Assuming presence of header
        //$rows = $csv->setOffset(1)->fetchAll();
        $rows = $csv->fetchAssoc(0);

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
            $name = $row['First Name'] . ' ' . $row['Last Name'];
            $email = $row['Email Address'];
            $phone_number = $row['Phone Number'];

            $member = User::whereHas('contacts', function ($query) use ($email, $phone_number) {
                $query
                ->where('contact', '=', $email)
                ->orWhere('contact', '=', $phone_number);
            })->first();

            if (!$member) {
                $member = User::firstOrCreate([
                    'name'     => $name,
                ]);
            }

            // Add email contact
            Contact::updateOrCreate([
                'user_id'     => $member['id'],
                'type'        => 'email',
                'contact'     => $email
            ], ['can_receive' => true]);

            // Add phone contact
            Contact::updateOrCreate([
                'user_id'     => $member['id'],
                'type'        => 'phone',
                'contact'     => $phone_number
            ], ['can_receive' => true]);

            $ids[$member['id']] = ['role' => 'member'];
        }

        $organization->members()->sync($ids, false);
    }
}
