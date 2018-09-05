<?php

namespace TenFour\Services;

use TenFour\Models\Organization;
use TenFour\Models\Subscription;
use TenFour\Models\CreditAdjustment;
use TenFour\Notifications\CreditsChanged;

use DB;
use App;
use Log;

class CreditService
{
    const CREDITS_NEW_ORGANIZATION = 0;
    const BASE_CREDITS_PER_MONTH = 100;
    const CREDITS_PER_USER_BUNDLE_PER_MONTH = 0;

    public function __construct()
    {
    }

    public function getBalance($organization_id) {
        $balance = DB::table('credit_adjustments')
            ->where('id', DB::raw("(SELECT MAX(id) FROM `credit_adjustments` WHERE `organization_id` = " . $organization_id . ")"))
            ->value('balance');

        if (is_null($balance)) {
            return 0;
        } else {
            return $balance;
        }
    }

    public function createStartingBalance($organization_id) {
        $creditAdjustment = new CreditAdjustment;
        $creditAdjustment->organization_id = $organization_id;
        $creditAdjustment->adjustment = self::CREDITS_NEW_ORGANIZATION;
        $creditAdjustment->balance = self::CREDITS_NEW_ORGANIZATION;
        $creditAdjustment->type = 'init';
        $creditAdjustment->save();
    }

    public function addCreditAdjustment($organization_id, $adjustment, $type = 'misc', $meta = null) {
        $creditAdjustment = new CreditAdjustment;
        $balance = 0;

        DB::transaction(function () use ($creditAdjustment, $organization_id, $adjustment, $type, $meta, $balance) {
            $creditAdjustment->organization_id = $organization_id;
            $creditAdjustment->adjustment = $adjustment;
            $creditAdjustment->balance = 0;
            $creditAdjustment->type = $type;
            $creditAdjustment->meta = $meta;
            $creditAdjustment->save();

            $balance = $this->syncBalance($creditAdjustment);
        });

        Organization::findOrFail($organization_id)->owner()->notify(new CreditsChanged($balance));

        return $creditAdjustment;
    }

    protected function syncBalance($creditAdjustment) {
        $balance = DB::table('credit_adjustments')
            ->where('organization_id', $creditAdjustment['organization_id'])
            ->sum('adjustment');

        $creditAdjustment->update(['balance' => $balance]);

        return $balance;
    }

    public function hasSufficientCredits($check_in) {
        $contact_repo = \App::make('TenFour\Contracts\Repositories\ContactRepository');
        $org_repo = \App::make('TenFour\Contracts\Repositories\OrganizationRepository');

        $organization = $org_repo->find($check_in['organization_id']);
        $available_credits = $organization['credits'];

        if ($check_in['send_via'] == ['app']) {
            return true;
        }

        $recipient_ids = array_map(function ($recipient) {
            return $recipient['id'];
        }, $check_in['recipients']);

        $contacts = $contact_repo->getByUserId($recipient_ids, $check_in['send_via']);

        foreach ($contacts as $contact) {
            // TODO calculate also for voice
            // TODO check if contact canReceiveCheckIn()
            if ($contact['type'] === 'phone') {
                // TODO this is where we have to calculate different credit for different contact region and operator
                $available_credits--;
            }
        }

        return $available_credits >= 0;
    }

    public function clearCredits($organization_id) {
        $balance = $this->getBalance($organization_id);
        return $this->addCreditAdjustment($organization_id, -$balance, 'clear');
    }
}
