<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Models\Organization;
use TenFour\Models\User;
use TenFour\Models\Contact;
use TenFour\Models\CheckIn;
use TenFour\Services\AnalyticsService;
use TenFour\Notifications\CheckIn as CheckInNotification;

use DB;
use Log;
use App;
use Exception;
use Statsd;

class SendCheckIn implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue, Queueable, SerializesModels;

    public $check_in, $organization;
    protected $check_in_repo, $notification;

    /**
     * Create a new job instance.
     *
     * @param array $check_in
     *
     * @return void
     */
    public function __construct(array $check_in)
    {
        $this->check_in = $check_in;
    }

    public function failed(Exception $exception)
    {
        Log::warning($exception);
        Statsd::increment('worker.sendcheckin.failed');
        app('sentry')->captureException($exception);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CheckInRepository $check_in_repo, ContactRepository $contact_repo)
    {
        $this->check_in_repo = $check_in_repo;
        $this->organization = Organization::findOrFail($this->check_in['organization_id']);
        
        if ($this->isDisabledDueToComplaints()) {
            Log::warning('Cannot send check-in for ' . $this->organization->name . ' because complaints exceed threshold');
            return;
        }

        $this->enforceFreePlanChannels();

        $notification = new CheckInNotification(
            $this->check_in,
            $this->organization);

        foreach($this->check_in['recipients'] as $recipient)
        {
            $this->markPreviousCheckInUnresponsive($recipient);
            $this->markCurrentCheckInWaiting($recipient);
            $this->setRecipientReplyToken($recipient);

            $contacts = $contact_repo->getByUserId($recipient['id'], $this->check_in['send_via']);

            foreach($contacts as $contact)
            {
                $contact = Contact::findOrFail($contact['id']);

                if ($contact->canReceiveCheckIn()) {
                    $contact->notify($notification);
                } else {
                    Log::warning("Will not send a check-in to an invalid or blocked contact: " . $contact->contact);
                }
            }

            User::findOrFail($recipient['id'])->notify($notification);
        }

        CheckIn::findOrFail($this->check_in['id'])->notify($notification);
        $this->deductCredits();
        $this->sendAnalytics();
    }

    private function isDisabledDueToComplaints() {
        $complaint_count = $this->check_in_repo->getComplaintCountByOrg($this->check_in['organization_id']);
        return $complaint_count >= config('tenfour.messaging.complaint_threshold');
    }

    private function enforceFreePlanChannels() {
        $subscription = $this->organization->currentSubscription();

        if (!$subscription || $subscription->plan_id === config("chargebee.plans.free")) {
            if (in_array('app', $this->check_in['send_via'])) {
               $this->check_in['send_via'] = ['app'];
            } else {
                $this->check_in['send_via'] = [];
            }
        }
    }

    private function markPreviousCheckInUnresponsive($recipient)
    {
        $unreplied_check_in_id = $this->check_in_repo->getLastUnrepliedByUser($recipient['id']);

        if ($unreplied_check_in_id) {
            $this->check_in_repo->updateRecipientStatus($unreplied_check_in_id, $recipient['id'], 'unresponsive');
        }
    }

    private function markCurrentCheckInWaiting($recipient)
    {
        $this->check_in_repo->updateRecipientStatus($this->check_in['id'], $recipient['id'], 'waiting');
    }

    private function setRecipientReplyToken($recipient)
    {
        return $this->check_in_repo->setReplyToken($this->check_in['id'], $recipient['id']);
    }

    private function sendAnalytics()
    {
        (new AnalyticsService())->track('CheckIn Sent', [
            'org_id'            => $this->check_in['organization_id'],
            'check_in_id'       => $this->check_in['id'],
            'total_recipients'  => count($this->check_in['recipients']),
        ]);

        Statsd::increment('checkin.sent');
    }

    private function getUnchargedMessages() {
        return DB::table('check_in_messages')
            ->where('check_in_id', $this->check_in['id'])
            ->whereNull('credit_adjustment_id');
    }

    private function deductCredits()
    {
        $uncharged_messages = $this->getUnchargedMessages();
        $sum = $uncharged_messages->sum('credits');

        if ($sum) {
            $credit_adjustment = App::make('TenFour\Services\CreditService')->addCreditAdjustment(
                $this->check_in['organization_id'],
                0-$sum,
                'check-in',
                [
                    'credits'     => $sum,
                    'recipients'  => count($this->check_in['recipients']),
                    'contacts'    => $uncharged_messages->count(),
                    'check_in_id' => $this->check_in['id'],
                ]);

            $uncharged_messages->update(['credit_adjustment_id' => $credit_adjustment->id]);
        }
    }

}
