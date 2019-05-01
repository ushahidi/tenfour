<?php

namespace TenFour\Http\Controllers\Api\First;
use TenFour\Http\Response;
use Dingo\Api\Auth\Auth;
use TenFour\Contracts\Repositories\ScheduledCheckinRepository;
use TenFour\Http\Transformers\ScheduledCheckinTransformer;
use TenFour\Http\Requests\CheckIn\GetScheduledCheckinsRequest;
use Symfony\Component\HttpFoundation\Request;
use TenFour\Http\Requests\CheckIn\DeleteScheduledCheckinRequest;

/**
 * @Resource("Checkins", uri="/api/v1/organizations/{org_id}/checkins/scheduled")
 */
class ScheduledCheckinController extends ApiController
{
    public function __construct(ScheduledCheckinRepository $scheduled_checkins, Auth $auth, Response $response)
    {
        $this->scheduled_checkins = $scheduled_checkins;
        $this->auth = $auth;
        $this->response = $response;
    }
  /**
     * Get all pending scheduled check-ins for an organization
     *
     * @Get("/{?offset,limit,template}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("org_id", type="number", required=true, description="Organization id"),
     *     @Parameter("offset", default=0),
     *     @Parameter("limit", default=0),
     *     @Parameter("template", type="boolean", default=false, description="only retrieve check-in templates")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     * {
     *   "scheduled_checkins": 
     *   [
     *       {
     *           "id": 3,
     *           "scheduled": 1,
     *           "frequency": "hourly",
     *           "starts_at": "2019-04-23 22:35:51",
     *           "expires_at": "2019-04-24 22:35:00",
     *           "remaining_count": 1,
     *           "created_at": "2019-04-23 22:35:51",
     *           "updated_at": "2019-04-23 22:36:03",
     *           "checkins": {
     *               "id": 29,
     *               "message": "my sch",
     *               "organization_id": 1,
     *               "status": "pending",
     *               "sent": 0,
     *               "created_at": "2019-04-23 22:35:51",
     *               "updated_at": "2019-04-23 22:35:51",
     *               "user_id": 1,
     *               "send_via": [
     *                   "app"
     *               ],
     *               "complaint_count": 0,
     *               "self_test_check_in": 0,
     *               "everyone": 0,
     *               "template": 1,
     *               "scheduled_checkin_id": 3,
     *               "send_at": null,
     *               "replies": []
     *           }
     *        }
     *    ]
     * }
     * })
     *
     * @param Request $request
     * @param org_id
     * @return Response
     */
    public function pending(GetScheduledCheckinsRequest $request, $organization_id)
    {
        $user_id = null;
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);
        if ($request->query('user') === 'me') {
            $user_id = $this->auth->user()['id'];
        } else {
            $user_id = $request->query('user');
        }
        $scheduled_checkin = $this->scheduled_checkins->pending(
            $request->route('organization'),
            $user_id,
            false,
            $offset,
            $limit
        );

        return $this->response->collection($scheduled_checkin, new ScheduledCheckinTransformer, 'scheduled_checkins');
    }
    /**
     * Get all scheduled check-ins for an organization
     *
     * @Get("/{?offset,limit,template}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("org_id", type="number", required=true, description="Organization id"),
     *     @Parameter("offset", default=0),
     *     @Parameter("limit", default=0),
     *     @Parameter("template", type="boolean", default=false, description="only retrieve check-in templates")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     * {
     *   "scheduled_checkins": 
     *   [
     *       {
     *           "id": 3,
     *           "scheduled": 1,
     *           "frequency": "hourly",
     *           "starts_at": "2019-04-23 22:35:51",
     *           "expires_at": "2019-04-24 22:35:00",
     *           "remaining_count": 1,
     *           "created_at": "2019-04-23 22:35:51",
     *           "updated_at": "2019-04-23 22:36:03",
     *           "checkins": {
     *               "id": 29,
     *               "message": "my sch",
     *               "organization_id": 1,
     *               "status": "pending",
     *               "sent": 0,
     *               "created_at": "2019-04-23 22:35:51",
     *               "updated_at": "2019-04-23 22:35:51",
     *               "user_id": 1,
     *               "send_via": [
     *                   "app"
     *               ],
     *               "complaint_count": 0,
     *               "self_test_check_in": 0,
     *               "everyone": 0,
     *               "template": 1,
     *               "scheduled_checkin_id": 3,
     *               "send_at": null,
     *               "replies": []
     *           }
     *        }
     *    ]
     * }
     * })
     *
     * @param Request $request
     * @param org_id
     * @return Response
     */
    public function all(GetScheduledCheckinsRequest $request, $organization_id)
    {
        $user_id = null;
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);
        if ($request->query('user') === 'me') {
            $user_id = $this->auth->user()['id'];
        } else {
            $user_id = $request->query('user');
        }
        $scheduled_checkin = $this->scheduled_checkins->all(
            $request->route('organization'),
            $user_id,
            $offset,
            $limit
        );

        return $this->response->collection($scheduled_checkin, new ScheduledCheckinTransformer, 'scheduled_checkins');
    }
    /**
     * Delete a scheduled check in
     *
     * @Delete("/organizations/{organization}/scheduled_checkins/{id}")
     * @Parameters({
     *   @Parameter("organization",required=true, description="The org id")
     *   @Parameter("id",required=true, description="The scheduled checkin to delete")
     * })
     *
     * @Versions({"v1"})
     * @Request({}, headers={"Authorization": "Bearer token"})
     * @Response(200, body={})
     *
     * @param Request $request
     * @return Response
     */
    public function delete(DeleteScheduledCheckinRequest $request, $organization, $id)
    {
        $request = $request->all();
        $deleted = $this->scheduled_checkins->find($id)->delete();
        if ($deleted) {
            return response()->json(['status' => 'Deleted']);
        } else {
            return response()->json(['status' => 'Not deleted', 400]);
        }
    }

}
