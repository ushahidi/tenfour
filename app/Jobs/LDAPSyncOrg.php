<?php

namespace TenFour\Jobs;

use TenFour\Models\Organization;
use TenFour\Models\User;
use TenFour\Models\Group;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Contracts\Repositories\PersonRepository;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Contracts\Repositories\GroupRepository;
use TenFour\Notifications\LDAPSyncFailed;
use TenFour\Notifications\LDAPSyncSucceeded;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberFormat;
use Carbon\Carbon;
use Log;
use DB;
use Exception;

class LDAPSyncOrg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $organization_id, $organization, $setting, $ds;
    protected $user_count, $group_count, $group_map;
    protected $people, $groups, $contacts, $organizations;
    protected $user_attributes = array("dn", "ou", "cn", "memberof", "telephoneNumber", "mail", "photo", "title", "streetAddress");

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($organization_id)
    {
        $this->organization_id = $organization_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrganizationRepository $organizations, PersonRepository $people, ContactRepository $contacts, GroupRepository $groups)
    {
        $this->organization = Organization::findOrFail($this->organization_id);
        $this->setting = $organizations->getSetting($this->organization_id, 'ldap');

        if (!$this->setting) {
            Log::warning("[LDAP] " . $this->organization->subdomain . " is misconfigured.");
            return;
        }

        if (!$this->setting->enabled) {
            Log::debug("[LDAP] " . $this->organization->subdomain . " is not enabled.");
            return;
        }

        $this->verify_settings();

        $this->ldap_connect();

        $this->people = $people;
        $this->groups = $groups;
        $this->contacts = $contacts;
        $this->organizations = $organizations;

        $this->user_count = 0;
        $this->group_count = 0;
        $this->group_map = [];

        DB::transaction(function () {
            $this->delete_stale_users();
            $this->delete_stale_groups();
            $this->sync_groups();
            $this->sync_users();
        });

        $this->send_success_notification();
        $this->update_last_sync_at();

        $this->ldap_close();
    }

    public function failed(Exception $exception)
    {
        Log::error($exception);

        $organization = Organization::findOrFail($this->organization_id);
        $organization->owner()->notify(new LDAPSyncFailed($organization, $exception));
    }

    private function verify_settings() {
        if (!$this->setting->url) {
            throw new Exception('Missing URL setting');
        } else if (!$this->setting->base_dn) {
            throw new Exception('Missing Base DN setting');
        } else if (!$this->setting->user_filter) {
            throw new Exception('Missing User Filter setting');
        } else if (!$this->setting->user_filter) {
            throw new Exception('Missing Group Filter setting');
        }
    }

    private function ldap_connect() {
        Log::debug("[LDAP] " . $this->organization->subdomain . " connecting to '" . $this->setting->url . "'");
        $this->ds = ldap_connect($this->setting->url);

        if (!$this->ds) {
            throw new Exception('Cannot connect to LDAP');
        }

        ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ds, LDAP_OPT_REFERRALS, FALSE);

        Log::debug("[LDAP] " . $this->organization->subdomain . " authenticating with '" . $this->setting->user . "' ");
        $r=ldap_bind($this->ds, $this->setting->user, $this->setting->password);

        if (!$r) {
            throw new Exception('Cannot bind to LDAP');
        }
    }

    // *** loop through all ldap users, delete if doesn't exist on server
    private function delete_stale_users() {

        $ldap_users = User::where('organization_id', '=', $this->organization_id)
            ->where('source', '=', 'ldap')
            ->get();

        Log::debug("[LDAP] " . $this->organization->subdomain . " has " . count($ldap_users) . " existing LDAP users");

        for ($i=0; $i<count($ldap_users); $i++) {
            $sr=ldap_search($this->ds, $ldap_users[$i]['source_id'], '(objectclass=*)');

            if (ldap_count_entries($this->ds, $sr) === 0) {
                Log::debug("[LDAP] " . $this->organization->subdomain . ' Deleting stale person ' . $ldap_users[$i]['source_id']);
                $this->people->delete($this->organization_id, $ldap_users[$i]['id']);
            }
        }
    }

    // *** loop through all ldap groups, delete if doesn't exist on server
    private function delete_stale_groups() {
        $ldap_groups = Group::where('organization_id', '=', $this->organization_id)
            ->where('source', '=', 'ldap')
            ->get();

        Log::debug("[LDAP] " . $this->organization->subdomain . " has " . count($ldap_groups) . " existing LDAP groups");

        for ($i=0; $i<count($ldap_groups); $i++) {
            $sr=ldap_search($this->ds, $ldap_groups[$i]['source_id'], '(objectclass=*)');

            if (ldap_count_entries($this->ds, $sr) === 0) {
                Log::debug("[LDAP] " . $this->organization->subdomain . ' Deleting stale group ' . $ldap_groups[$i]['source_id']);
                $this->groups->delete($this->organization_id, $ldap_groups[$i]['id']);
            }
        }
    }

    private function sync_groups() {
        if (!$this->setting->group_filter) {
            return;
        }

        Log::debug("[LDAP] " . $this->organization->subdomain . " syncing groups");

        $sr=ldap_search($this->ds, $this->setting->base_dn, $this->setting->group_filter);
        $group_entries = ldap_get_entries($this->ds, $sr);

        Log::debug("[LDAP] " . $this->organization->subdomain . " has " . $group_entries["count"] . " remote ldap groups");

        for ($i=0; $i<$group_entries["count"]; $i++) {
            $group_cn = $group_entries[$i]["cn"][0];
            $group_dn = trim($group_entries[$i]["dn"]);

            Log::debug("[LDAP] " . $this->organization->subdomain . " has remote group: " . $group_dn);

            $group = $this->groups->findBySource($this->organization_id, 'ldap', $group_dn);

            if (!$group) {
                Log::debug("[LDAP] " . $this->organization->subdomain . ' remote ldap group DOES NOT exist locally');

                $group = $this->groups->create($this->organization_id, [
                    'name' => $group_cn,
                    'description' => '',
                    'members' => [],
                    'source' => 'ldap',
                    'source_id' => $group_dn
                ]);
            } else {
                Log::debug("[LDAP] " . $this->organization->subdomain . ' remote ldap group DOES exist locally');

                $this->groups->update($this->organization_id, [
                    'name' => $group_cn,
                    'description' => '',
                ], $group['id']);
            }

            $this->group_count++;
            $this->group_map[$group_dn] = $group['id'];
        }
    }

    private function sync_users() {
        Log::debug("[LDAP] " . $this->organization->subdomain . " syncing users " . $this->setting->user_filter);

        $sr=ldap_search($this->ds, $this->setting->base_dn, $this->setting->user_filter, $this->user_attributes);

        $user_entries = ldap_get_entries($this->ds, $sr);

        Log::debug("[LDAP] " . $this->organization->subdomain . " has " . $user_entries["count"] . " remote ldap users");

        for ($i=0; $i<$user_entries["count"]; $i++) {
            if (!isset($user_entries[$i]["dn"])) {
                continue;
            }

            $user_dn = $user_entries[$i]["dn"];
            $user_cn = $user_entries[$i]["cn"][0];
            $user_photo = isset($user_entries[$i]["photo"]) && count($user_entries[$i]["photo"]) ? $user_entries[$i]["photo"][0] : '';
            $user_title = isset($user_entries[$i]["title"]) && count($user_entries[$i]["title"]) ? $user_entries[$i]["title"][0] : '';
            $user_phones = isset($user_entries[$i]["telephonenumber"]) && count($user_entries[$i]["telephonenumber"]) ? $user_entries[$i]["telephonenumber"] : [];
            $user_emails = isset($user_entries[$i]["mail"]) && count($user_entries[$i]["mail"]) ? $user_entries[$i]["mail"] : [];
            $user_addresses = isset($user_entries[$i]["streetaddress"]) && count($user_entries[$i]["streetaddress"]) ? $user_entries[$i]["streetaddress"] : [];
            $user_groups = isset($user_entries[$i]["memberof"]) && count($user_entries[$i]["memberof"]) ? $user_entries[$i]["memberof"] : [];

            Log::debug($user_dn);

            $person = $this->people->findBySource($this->organization_id, 'ldap', $user_dn);

            if (!$person) {
                Log::debug("[LDAP] " . $this->organization->subdomain . " remote ldap person DOES NOT exist locally");

                $person = $this->people->create($this->organization_id, [
                    'name' => $user_cn,
                    'person_type' => 'external',
                    'source' => 'ldap',
                    'source_id' => $user_dn,
                    'profile_picture' => $user_photo,
                    'description' => $user_title,
                ]);

                $person = User::where('id', $person['id'])->firstOrFail();
            } else {
                Log::debug("[LDAP] " . $this->organization->subdomain . " remote ldap person DOES exist locally");

                $this->people->update($this->organization_id, [
                    'name' => $user_cn,
                    'person_type' => 'external',
                    'source' => 'ldap',
                    'source_id' => $user_dn,
                    'profile_picture' => $user_photo,
                    'description' => $user_title,
                ], $person['id']);
            }

            if (isset($user_phones) && count($user_phones)) {
                $this->sync_user_phones($person, $user_phones);
            }

            if (isset($user_emails) && count($user_emails)) {
                $this->sync_user_emails($person, $user_emails);
            }

            if (isset($user_addresses) && count($user_addresses)) {
                $this->sync_user_addresses($person, $user_addresses);
            }

            if (isset($user_groups) && count($user_groups)) {
                $this->sync_user_groups($person, $user_groups);
            }

            $this->user_count++;
        }
    }

    private function sync_user_phones($person, $user_phones) {
        try {
            $phone_meta = $this->normalizePhoneNumber($user_phones[0]);
        } catch (NumberParseException $exception) {
            Log::warning($exception); // TODO handle this better?
            return;
        }

        $phone_contacts = $this->contacts->getByUserId($person['id'], ['phone']);

        if (count($phone_contacts)) {
            $phone_contact = $phone_contacts[0];
            $phone_contact['contact'] = $phone_meta['e164'];
            $phone_contact['meta'] = $phone_meta;
            $this->contacts->update($phone_contact, $phone_contact['id']);
        } else {
            $this->contacts->create([
                'type' => 'phone',
                'contact' => $phone_meta['e164'],
                'user_id' => $person['id'],
                'organization_id' => $this->organization_id,
                'preferred' => 1,
                'meta' => $phone_meta
            ]);
        }
    }

    private function sync_user_emails($person, $user_emails) {
        $email_address = $this->normalizeEmailAddress($user_emails[0]);

        $email_contacts = $this->contacts->getByUserId($person['id'], ['email']);

        if (count($email_contacts)) {
            $email_contact = $email_contacts[0];
            $email_contact['contact'] = $email_address;
            $this->contacts->update($email_contact, $email_contact['id']);
        } else {
            $this->contacts->create([
                'type' => 'email',
                'contact' => $email_address,
                'user_id' => $person['id'],
                'organization_id' => $this->organization_id,
                'preferred' => 1
            ]);
        }
    }

    private function sync_user_addresses($person, $user_addresses) {
        $address = $user_addresses[0];

        $address_contacts = $this->contacts->getByUserId($person['id'], ['address']);

        if (count($address_contacts)) {
            $address_contact = $address_contacts[0];
            $address_contact['contact'] = $address;
            $this->contacts->update($address_contact, $address_contact['id']);
        } else {
            $this->contacts->create([
                'type' => 'address',
                'contact' => $address,
                'user_id' => $person['id'],
                'organization_id' => $this->organization_id,
                'preferred' => 1
            ]);
        }
    }

    private function sync_user_groups($person, $user_groups) {
        $person_groups = [];

        for ($j=0; $j<count($user_groups); $j++) {
            if (isset($user_groups[$j])) {
                Log::debug("user is part of the group: " . $user_groups[$j]);
                if ($this->group_map[$user_groups[$j]]) {
                    $group = $this->group_map[$user_groups[$j]];
                    array_push($person_groups, $group);
                }
            }
        }

        $person->groups()->sync($person_groups);
    }

    private function send_success_notification() {
        if (!isset($this->setting->latest_sync_at)) {
            $this->organization->owner()->notify(new LDAPSyncSucceeded($this->organization, $this->user_count, $this->group_count));
        }
    }

    private function update_last_sync_at() {
        $this->setting->latest_sync_at = (new Carbon())->toAtomString();
        $this->organizations->setSetting($this->organization_id, 'ldap', $this->setting);
    }

    private function normalizePhoneNumber($phoneNumber) {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $number = $phoneNumberUtil->parse($phoneNumber, null);

        $national_number = $number->getNationalNumber();
        $country_code = $number->getCountryCode();
        $carrier = PhoneNumberToCarrierMapper::getInstance()
                       ->getNameForNumber($number, 'en');

        return [
            'e164'            => $phoneNumberUtil->format($number, PhoneNumberFormat::E164),
            'national_number' => $national_number,
            'country_code'    => $country_code,
            'carrier'         => $carrier,
        ];
    }

    private function ldap_close() {
        Log::debug("[LDAP] " . $this->organization->subdomain . " closing connection");

        ldap_close($this->ds);
    }

    private function normalizeEmailAddress($emailAddress) {
        return trim($emailAddress);
    }
}
