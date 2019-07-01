<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Http\Response;

use Dingo\Api\Auth\Auth;
use App;
use Illuminate\Support\Facades\DB;
use TenFour\Contracts\Repositories\AlertSourceRepository;
use TenFour\Contracts\Repositories\AlertFeedRepository;
use TenFour\Contracts\Repositories\AlertSubscriptionRepository;
use TenFour\Contracts\Repositories\AlertFeedEntryRepository;
use TenFour\Http\Transformers\AlertSourceTransformer;
use TenFour\Http\Requests\GetAlertSourcesRequest;
use TenFour\Http\Requests\GetAlertSubscriptionsRequest;
use TenFour\Http\Transformers\AlertSubscriptionTransformer;
use TenFour\Http\Requests\SaveAlertFeedRequest;
use TenFour\Http\Transformers\AlertFeedTransformer;
use TenFour\Http\Requests\GetAlertFeedsRequest;

/**
 * @Resource("EmergencyAlerts", uri="/api/v1/emergencyAlerts")
 */
class EmergencyAlertController extends ApiController
{
    public function __construct(AlertFeedRepository $alertFeedRepo, AlertFeedEntryRepository $alertFeedEntryRepo, AlertSourceRepository $alertSourceRepo, AlertSubscriptionRepository $alertSubscriptionRepo, CheckInRepository $check_ins, Auth $auth, Response $response)
    {
        $this->alertFeedRepo = $alertFeedRepo;
        $this->alertFeedEntryRepo = $alertFeedEntryRepo;
        $this->alertSourceRepo = $alertSourceRepo;
        $this->check_ins = $check_ins;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Get all alert sources available for this organization
    **/
    public function sources(GetAlertSourcesRequest $request, $organization_id)
    {
        $sources = $this->alertSourceRepo->all(
            $request->input('organization', false),
            $request->input('enabled', null)
        );
        return $this->response->collection($sources, new AlertSourceTransformer, 'alerts');
    }

    /**
     * Get all alert sources available for this organization
    **/
    public function addFeed(SaveAlertFeedRequest $request, $organization_id)
    {
        $feed = $this->alertFeedRepo->create(
            $request->input()
        );
        return $this->response->item($feed, new AlertFeedTransformer, 'feed');
    }

    /**
     * Get all alert sources available for this organization
    **/
    public function getFeed(GetAlertFeedsRequest $request, $organization_id)
    {
        $feed = $this->alertFeedRepo->find(
            $request->route("id")
        );
        return $this->response->item($feed, new AlertFeedTransformer, 'feed');
    }

    /**
     * Get all alert sources available for this organization
    **/
    public function getFeeds(GetAlertFeedsRequest $request, $organization_id)
    {
        $feed = $this->alertFeedRepo->all(
            $request->route("organization")
        );
        return $this->response->item($feed, new AlertFeedTransformer, 'feeds');
    }
    /**
     * Subscribe an organization's groups and people to receive alerts from a source
    **/
    public function subscribe(CreateAlertSubscriptionRequest $request, $organization_id)
    {
        $sources = $this->alertSubscriptionRepo->create(
            $request->input('organization', false)
        );//TODO create things
        return $this->response->collection($sources, new AlertSubscriptionTransformer, 'alerts');
    }


    /**
     * Subscribe an organization's groups and people to receive alerts from a source
    **/
    public function subscriptions(GetAlertSubscriptionsRequest $request, $organization_id)
    {
        $sources = $this->alertSourceRepo->all(
            $request->input('organization', false)
        );//TODO create things
        return $this->response->collection($sources, new AlertSourceTransformer, 'alerts');
    }
}
