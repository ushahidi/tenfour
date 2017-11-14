<?php

namespace RollCall\Console\Commands;

use RollCall\Models\User;
use RollCall\Models\Contact;
use RollCall\Jobs\SendInvite;
use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Contracts\Repositories\ContactRepository;

use Illuminate\Support\Facades\Hash;
use Illuminate\Console\Command;

class OrgAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'org:admin {subdomain} {email} {--remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add/remove a Ushahidi admin account to an organization';

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
    public function handle(OrganizationRepository $organizations, PersonRepository $people, ContactRepository $contacts)
    {
        $organization = $organizations->findBySubdomain($this->argument('subdomain'));

        if ($this->option('remove')) {
            $deleteContact = $contacts->getByContact($this->argument('email'), $organization['id']);
            User::where('id', $deleteContact['user_id'])->delete();
            $this->info("The user '" . $this->argument('email') . "' has been deleted.");
            return;
        }

        $member = User::firstOrCreate([
            'name'            => 'Ushahidi Admin',
            'organization_id' => $organization['id'],
        ]);

        $member->organization_id = $organization['id'];
        $member->role = 'admin';
        $member->save();

        $contact = Contact::firstOrCreate([
            'type'            => 'email',
            'contact'         => $this->argument('email'),
            'preferred'       => 1,
            'user_id'         => $member->id,
            'organization_id' => $organization['id'],
        ]);

        $user = $people->find($organization['id'], $member->id);
        $user['invite_token'] = Hash::Make(config('app.key'));
        $user['invite_sent'] = true;
        $people->update($organization['id'], $user, $user['id']);

        dispatch(new SendInvite($user, $organization));

        $this->info("An invite has been sent to '" . $this->argument('email') . "'");
    }
}
