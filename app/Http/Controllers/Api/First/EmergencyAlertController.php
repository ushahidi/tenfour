<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Http\Response;
use TenFour\Jobs\SendCheckIn;
use TenFour\Services\CreditService;
use TenFour\Notifications\CheckInChanged;
use TenFour\Models\CheckIn;

use Dingo\Api\Auth\Auth;
use App;
use Illuminate\Support\Facades\DB;
use TenFour\Contracts\Repositories\AlertSourceRepository;
use TenFour\Contracts\Repositories\AlertFeedRepository;
use TenFour\Http\Transformers\AlertSourceTransformer;
use TenFour\Http\Requests\EmergencyAlerts\GetAlertSourcesRequest;
use TenFour\Http\Requests\EmergencyAlerts\GetAlertSubscriptionsRequest;
use TenFour\Http\Transformers\AlertSubscriptionTransformer;

/**
 * @Resource("EmergencyAlerts", uri="/api/v1/emergencyAlerts")
 */
class EmergencyAlertController extends ApiController
{
    public function __construct(AlertFeedRepository $alertFeedRepo, AlertSourceRepository $alertSourceRepo, CheckInRepository $check_ins, Auth $auth, Response $response)
    {
        $this->alertFeedRepo = $alertFeedRepo;
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
     * Subscribe an organization's groups and people to receive alerts from a source
    **/
    public function subscribe(CreateAlertSubscriptionRequest $request, $organization_id)
    {
        $sources = $this->alertSourceRepo->all(
            $request->input('organization', false)
        );//TODO create things
        return $this->response->collection($sources, new AlertSourceTransformer, 'alerts');
    }


    /**
     * Subscribe an organization's groups and people to receive alerts from a source
    **/
    public function subscriptions(GetAlertSubscriptionsRequest $request, $organization_id)
    {
        $sources = $this->alertFeedRepo->create(
            $request->input('organization', false)
        );//TODO create things
        return $this->response->collection($sources, new AlertSubscriptionTransformer, 'alerts');
    }
}
