<?php

namespace RollCall\Services;

use RollCall\Models\Organization;
use RollCall\Models\Subscription;
use RollCall\Models\CreditAdjustment;
use DB;
use App;
use Log;

class CreditService
{
    const CREDITS_PER_USER_PER_MONTH = 5;

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
        $creditAdjustment->adjustment = 0;
        $creditAdjustment->balance = 0;
        $creditAdjustment->type = 'init';
        $creditAdjustment->save();
    }

    public function addCreditAdjustment($organization_id, $adjustment, $type = 'misc', $meta = null) {
        $creditAdjustment = new CreditAdjustment;

        DB::transaction(function () use ($creditAdjustment, $organization_id, $adjustment, $type, $meta) {
            $creditAdjustment->organization_id = $organization_id;
            $creditAdjustment->adjustment = $adjustment;
            $creditAdjustment->balance = 0;
            $creditAdjustment->type = $type;
            $creditAdjustment->meta = $meta;
            $creditAdjustment->save();

            $this->syncBalance($creditAdjustment);
        });

        return $creditAdjustment;
    }

    protected function syncBalance($creditAdjustment) {
        $balance = DB::table('credit_adjustments')
            ->where('organization_id', $creditAdjustment['organization_id'])
            ->sum('adjustment');

        $creditAdjustment->update(['balance' => $balance]);
    }

    public function expireCreditsOnUnpaid() {

        $expiredSubscriptions = Subscription
            ::where(function ($query) {
                $query->whereNotNull('next_billing_at')
                      ->where('next_billing_at', '<', date('Y-m-d H:i:s'));
            })
            ->orWhere(function ($query) {
                $query->whereNull('next_billing_at')
                      ->whereNotNull('trial_ends_at')
                      ->where('trial_ends_at', '<', date('Y-m-d H:i:s'));
            })
            ->with('organization')
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            DB::transaction(function () use ($subscription) {
                $balance = $this->getBalance($subscription->organization->id);
                $meta = ['previousBalance' => $balance];

                if ($balance !== 0) {
                    Log::info('Expiring credits for organization ' . $subscription->organization->id);
                    $this->addCreditAdjustment($subscription->organization->id, 0 - $balance, 'expire', $meta);
                }
            });
        }
    }

    public function hasSufficientCredits($rollcall) {
        $contact_repo = \App::make('RollCall\Contracts\Repositories\ContactRepository');
        $org_repo = \App::make('RollCall\Contracts\Repositories\OrganizationRepository');

        $organization = $org_repo->find($rollcall['organization_id']);
        $available_credits = $organization['credits'];

        if ($rollcall['send_via'] == ['apponly']) {
            return true;
        }

        $recipient_ids = array_map(function ($recipient) {
            return $recipient['id'];
        }, $rollcall['recipients']);

        $contacts = $contact_repo->getByUserId($recipient_ids, $rollcall['send_via']);

        foreach ($contacts as $contact) {
            if ($contact['type'] === 'phone') {
                // TODO this is where we calculate different credit for different contact region and operator
                $available_credits--;
            }
        }

        return $available_credits >= 0;
    }
}
