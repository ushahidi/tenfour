<?php

namespace TenFour\Http\Controllers\Api;

use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Http\Response;
use TenFour\Http\Controllers\Api\First\ApiController;
use Dingo\Api\Auth\Auth;
use App;
use Illuminate\Support\Facades\DB;
use TenFour\Models\CheckIn;
use TenFour\Contracts\Repositories\AlertSourceRepository;
use TenFour\Contracts\Repositories\AlertFeedRepository;
use TenFour\Contracts\Repositories\AlertSubscriptionRepository;
use TenFour\Contracts\Repositories\AlertFeedEntryRepository;
use TenFour\Http\Transformers\AlertSourceTransformer;
use TenFour\Http\Requests\GetAlertSourcesRequest;
use TenFour\Http\Requests\GetAlertSubscriptionsRequest;
use TenFour\Http\Requests\EmergencyAlertWebhookRequest;

use TenFour\Http\Transformers\AlertSubscriptionTransformer;
use TenFour\Http\Requests\SaveAlertFeedRequest;
use TenFour\Http\Transformers\AlertFeedTransformer;
use TenFour\Http\Requests\GetAlertFeedsRequest;
use TenFour\Models\AlertFeedEntry;

/**
 * @Resource("EmergencyAlerts", uri="/api/v1/emergencyAlerts")
 */
class EmergencyAlertWebhookController extends ApiController
{
    public function __construct(AlertFeedRepository $alertFeedRepo, AlertFeedEntryRepository $alertFeedEntryRepo, AlertSourceRepository $alertSourceRepo, AlertSubscriptionRepository $alertSubscriptionRepo, CheckInRepository $check_ins, Auth $auth, Response $response)
    {
        $this->alertFeedRepo = $alertFeedRepo;
        $this->alertFeedEntryRepo = $alertFeedEntryRepo;
        $this->alertSourceRepo = $alertSourceRepo;
        $this->check_ins = $check_ins;
        $this->auth = $auth;
        $this->response = $response;
        $this->alertSubscriptionRepo = $alertSubscriptionRepo;
    }

    /**
     * Notify of a new alert
    **/
    public function newAlertFeedEntry(EmergencyAlertWebhookRequest $request)
    {
        $feedEntry = $this->alertFeedEntryRepo->create(
            $request->input()
        );
        $subscribers = $this->notifySubscribersForFeedEntry($feedEntry);
        return $this->response->item($feedEntry, new AlertFeedTransformer, 'feedEntry');
    }

    private function notifySubscribersForFeedEntry(array $feedEntry) {
        $subscribers = $this->alertSubscriptionRepo->subscribers($feedEntry['feed_id']);
        foreach ($subscribers as $subscriber) {
            // automatic checkin
            if ($subscriber['checkin_template_id']) {
                $message = substr($feedEntry['title'] . '\n' . $feedEntry['body'], 0, 140);
                $checkInTemplate = $this->check_ins->update(['message' => $message], $subscriber['checkin_template_id']);
                //TODO generate bit.ly with the full alert text to attach
                $now = new \DateTime('now');
                $checkInTemplate['send_at'] = $now->format('Y-m-d H:i:s');
                unset($checkInTemplate['id']);
                unset($checkInTemplate['created_at']);
                unset($checkInTemplate['updated_at']);
                unset($checkInTemplate['deleted_at']);
                unset($checkInTemplate['template']);
                unset($checkInTemplate['users']);
                $this->check_ins->create($checkInTemplate);
            }
            //TODO notify admin via push notification of the new alert
        }
    }
}
