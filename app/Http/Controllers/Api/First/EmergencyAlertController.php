<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Http\Requests\CheckIn\GetCheckInsRequest;
use TenFour\Http\Transformers\CheckInTransformer;
use TenFour\Http\Response;
use TenFour\Jobs\SendCheckIn;
use TenFour\Services\CreditService;
use TenFour\Notifications\CheckInChanged;
use TenFour\Models\CheckIn;

use Illuminate\Support\Facades\Notification;
use Dingo\Api\Auth\Auth;
use App;
use TenFour\Models\ScheduledCheckin;
use Illuminate\Support\Facades\DB;
use TenFour\Contracts\Repositories\AlertSourceRepository;
use TenFour\Contracts\Repositories\AlertFeedRepository;

/**
 * @Resource("EmergencyAlerts", uri="/api/v1/emergencyAlerts")
 */
class EmergencyAlertController extends ApiController
{
    public function __construct(AlertFeedRepository $alertFeedRepo, AlertSourceRepository $alertSourceRepo,  CheckInRepository $check_ins, Auth $auth, Response $response, CreditService $creditService)
    {
        $this->alertFeedRepo = $alertFeedRepo;
        $this->alertSourceRepo = $alertSourceRepo;
        $this->check_ins = $check_ins;
        $this->auth = $auth;
        $this->response = $response;
        $this->creditService = $creditService;
    }

    /**
     * Get all alert sources available for this organization
    **/
    public function all(GetAlertSourcesRequest $request, $organization_id)
    {
        $user_id = null;
        if ($request->query('user') === 'me') {
            $user_id = $this->auth->user()['id'];
        } else {
            $user_id = $request->query('user');
        }

        $sources = $this->alertSourceRepo->all(
            $request->input('organization', false),
            $this->auth->user()['id']
        );

        return $this->response->collection($sources, new AlertSourceTransformer, 'alerts');
    }


}
