<?php

namespace RollCall\Console\Commands;

use Illuminate\Console\Command;

use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Models\Contact;
use League\Csv\Reader;
use Illuminate\Support\Facades\Hash;

class ImportContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacts:import {csv_file} {org=Ushahidi} {--password=}';

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
        $subdomain = strtolower($this->argument('org'));

        $organization->update([
            'subdomain'  => $subdomain,
        ]);

        $password = $this->option('password');

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
                    'name'            => $name,
                    'password'        => $password,
                    'role'            => 'member'

                ]);
            }

            $member->organization_id = $organization->id;
            $member->save();

            // Add email contact
            Contact::updateOrCreate([
                'user_id'     => $member['id'],
                'organization_id' => $organization->id,
                'type'        => 'email',
                'contact'     => $email,
                'subscribed'  => true,
                'unsubscribe_token' => Hash::Make(config('app.key')),
            ], ['preferred' => true]);

            // Add phone contact
            Contact::updateOrCreate([
                'user_id'     => $member['id'],
                'organization_id' => $organization->id,
                'type'        => 'phone',
                'contact'     => $phone_number,
                'subscribed'  => true,
            ], ['preferred' => true]);
        }

    }
}
