<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Contracts\Repositories\GroupRepository;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Http\Requests\Group\CreateGroupRequest;
use TenFour\Http\Requests\Group\DeleteGroupRequest;
use TenFour\Http\Requests\Group\GetGroupsRequest;
use TenFour\Http\Requests\Group\UpdateGroupRequest;
use TenFour\Http\Requests\Group\GetGroupRequest;
use TenFour\Http\Transformers\GroupTransformer;
use Dingo\Api\Auth\Auth;
use TenFour\Http\Response;

/**
 * @Resource("Groups", uri="/api/v1/organizations/{org_id}/groups")
 */

class GroupController extends ApiController
{
    public function __construct(GroupRepository $groups, OrganizationRepository $organizations, Auth $auth, Response $response)
    {
        $this->groups = $groups;
        $this->organizations = $organizations;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Create a group in an organization
     *
     * @Post("/")
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id")
     * })
     *
     * @Versions({"v1"})
     * @Request({
     *       "name": "Testing Group",
     *       "members": {
     *         {
     *             "id": 3
     *         },
     *         {
     *             "id": 1
     *         }
     *       }
     *  }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "group": {
     *         "id" : 4,
     *         "created_at": "2017-03-18 19:19:27",
     *         "name": "Testing Group",
     *         "organization": {
     *             "id": 2,
     *             "uri": "/organizations/2"
     *         },
     *         "members": {
     *             {
     *                 "id": 3,
     *                 "uri": "/users/3"
     *             },
     *             {
     *                 "id": 1,
     *                 "uri": "/users/1"
     *             }
     *         },
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function store(CreateGroupRequest $request, $organization_id)
    {
        $group = $this->groups->create($organization_id, $request->all());

        return $this->response->item($group, new GroupTransformer, 'group');
    }

    /**
     * Delete a group from an organization
     *
     * @Delete("/{group_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("group_id", type="number", required=true, description="Group id")
     * })
     *
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "group": {
     *         "name": "Test Group",
     *         "organization_id": 2,
     *         "updated_at": null
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function destroy(DeleteGroupRequest $request, $organization_id, $group_id)
    {
        return $this->response->item($this->groups->delete($organization_id, $group_id),
                                     new GroupTransformer, 'group');
    }

    /**
     * Get all groups for an organization
     *
     * @Get("/{?offset,limit}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("offset", default=0),
     *     @Parameter("limit", default=0)
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "groups": {
     *         {
     *             "name": "Test Group 1",
     *             "created_at": null,
     *             "id": 1,
     *             "organization": {
     *                 "id": 2,
     *                 "uri": "/organizations/2"
     *             },
     *             "members": {
     *                 {
     *                     "id": 1,
     *                     "uri": "/users/1"
     *                 }
     *             },
     *             "updated_at": null,
     *             "uri": "/organizations/2/groups/1",
     *         },
     *         {
     *             "name": "Test Group 2",
     *             "created_at": null,
     *             "id": 2,
     *             "organization": {
     *                 "id": 2,
     *                 "uri": "/organizations/2"
     *             },
     *             "members": {
     *                 {
     *                     "id": 1,
     *                     "uri": "/users/1"
     *                 }
     *             },
     *             "updated_at": null,
     *             "uri": "/organizations/2/groups/2",
     *         },
     *         {
     *             "name": "Test Group 3",
     *             "created_at": null,
     *             "id": 3,
     *             "organization": {
     *                 "id": 2,
     *                 "uri": "/organizations/2"
     *             },
     *             "members": {
     *                 {
     *                     "id": 1,
     *                     "uri": "/users/1"
     *                 }
     *             },
     *             "updated_at": null,
     *             "uri": "/organizations/2/groups/3",
     *         },
     *     }
     * })
     *
     * @param Request $request
     * @param org_id
     * @return Response
     */
    public function index(GetGroupsRequest $request, $organization_id)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);

        return $this->response->collection($this->groups->all($organization_id, $offset, $limit),
                                           new GroupTransformer, 'groups');
    }
    /**
     * Get a single group
     *
     * @Get("/{group_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("group_id", type="number", required=true, description="Group id")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "group": {
     *         "id": 1,
     *         "name": "Test Group 1",
     *         "organization": {
     *             "id": 1,
     *             "uri": "/organizations/1"
     *         },
     *         "members": {
     *             {
     *                 "id": 1,
     *                 "uri": "/users/1"
     *             },
     *             {
     *                 "id": 2,
     *                 "uri": "/users/2"
     *             }
     *         },
     *         "updated_at": null,
     *         "uri": "/organizations/2/groups/1"
     *     }
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function show(GetGroupRequest $request, $organization_id, $id)
    {
        $group = $this->groups->find($organization_id, $id);
        return $this->response->item($group, new GroupTransformer, 'group');
    }
    /**
     * Update a group
     *
     * @Put("/{group_id}")
     * @Parameters({
     *   @Parameter("group_id", type="number", required=true, description="Group id")
     * })
     * @Versions({"v1"})
     * @Request({
     *     "organization_id": 2,
     *     "members": {
     *         {
     *             "id": 1
     *         },
     *         {
     *             "id": 2
     *         }
     *     },
     *     "name": "Test Group"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "group": {
     *         "created_at": null,
     *         "id": 1,
     *         "name": "Test Group",
     *         "organization": {
     *             "id": 2,
     *             "uri": "/organizations/2"
     *         },
     *         "members": {
     *             {
     *                 "id": 1,
     *                 "uri": "/users/1"
     *             },
     *             {
     *                 "id": 2,
     *                 "uri": "/users/2"
     *             }
     *         },
     *         "updated_at": "2017-03-18 19:32:34",
     *         "uri": "/organization/1/groups/1"
     *     }
     * })
     *
     * @param Request $request
     * @param int $id
     * @param org_id
     *
     * @return Response
     */
    public function update(UpdateGroupRequest $request, $organization_id, $id)
    {
        $group = $this->groups->update($organization_id, $request->all(), $id);

        return $this->response->item($group, new GroupTransformer, 'group');
    }

}
