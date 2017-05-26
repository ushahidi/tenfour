<?php

namespace RollCall\Services;

use RollCall\Models\Organization;
use RollCall\Models\CreditAdjustment;
use DB;
use App;

class CreditService
{
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

    public function addCreditAdjustment($organization_id, $adjustment, $type = 'misc') {
        DB::transaction(function () use ($organization_id, $adjustment, $type) {
            $creditAdjustment = new CreditAdjustment;
            $creditAdjustment->organization_id = $organization_id;
            $creditAdjustment->adjustment = $adjustment;
            $creditAdjustment->balance = 0;
            $creditAdjustment->type = $type;
            $creditAdjustment->save();

            $this->syncBalance($creditAdjustment);
        });
    }

    protected function syncBalance($creditAdjustment) {
        $balance = DB::table('credit_adjustments')
            ->where('organization_id', $creditAdjustment['organization_id'])
            ->sum('adjustment');

        CreditAdjustment::findOrFail($creditAdjustment['id'])->update(['balance' => $balance]);
    }

    public function expireCreditsOnUnpaid() {
        foreach (DB::table('organizations')->where('paid_until', '<', date('Y-m-d H:i:s'))->get() as $org) {
            DB::transaction(function () use ($org) {
                $balance = $this->getBalance($org->id);

                if ($balance !== 0) {
                    $this->addCreditAdjustment($org->id, 0 - $balance, 'expire');
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
