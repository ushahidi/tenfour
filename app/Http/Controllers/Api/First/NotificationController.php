<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Contracts\Repositories\NotificationRepository;
use TenFour\Http\Requests\Person\GetPersonRequest;
use TenFour\Http\Transformers\NotificationTransformer;
use TenFour\Http\Response;

/**
 * @Resource("Notifications", uri="/api/v1/organizations")
 */
class NotificationController extends ApiController
{
    public function __construct(Response $response, NotificationRepository $notifications)
    {
        $this->response = $response;
        $this->notifications = $notifications;
    }

    /**
     * List a person's notifications
     *
     * @Get("/{org_id}/people/{person_id}/notifications/{?offset,limit,unread}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id"),
     *   @Parameter("unread", type="number", required=false, default=0, description="Return only a person's unread notifications"),
     *   @Parameter("offset", type="number", required=false, default=0),
     *   @Parameter("limit", type="number", required=false, default=0)
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *   "notifications": {
     *          {
     *                "id": "386f1eae-e8af-4acc-9a9a-6cfd9b086670",
     *                "type": "TenFour\\Notifications\\PersonLeftOrganization",
     *                "notifiable_id": 8,
     *                "notifiable_type": "TenFour\\Models\\User",
     *                "data": {
     *                  "person_name": "David Test",
     *                  "person_id": 6,
     *                  "profile_picture": false,
     *                  "initials": "DT"
     *                },
     *                "read_at": null,
     *               "created_at": "2018-07-06 13:58:21",
     *                "updated_at": "2018-07-06 13:58:21"
     *          }
     *    }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function index(GetPersonRequest $request, $organization_id, $person_id)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);
        $unread = !!$request->input('unread', false);

        return $this->response->collection($this->notifications->all($person_id, $offset, $limit, $unread),
                                           new NotificationTransformer, 'notifications');
    }
}
