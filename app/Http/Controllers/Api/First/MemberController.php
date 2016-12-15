<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Http\Requests\Member\GetMemberRequest;
use RollCall\Http\Requests\Member\GetMembersRequest;
use RollCall\Http\Requests\Member\AddMemberRequest;
use RollCall\Http\Requests\Member\DeleteMemberRequest;
use RollCall\Http\Requests\Member\UpdateMemberRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\UserTransformer;
use RollCall\Http\Response;

/**
 * @Resource("Members", uri="/api/v1/organizations/{orgId}/members")
 */
class MemberController extends ApiController
{
    public function __construct(OrganizationRepository $organizations, Auth $auth, Response $response)
    {
        $this->organizations = $organizations;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Add member to an organization
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Request({
     *
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function store(AddMemberRequest $request, $organization_id)
    {
        return $this->response->item($this->organizations->addMember($request->all(), $organization_id),
                                     new UserTransformer, 'user');
    }

    /**
     * List members of an organization
     *
     * @Get("/")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function index(GetMemberRequest $request, $organization_id)
    {
        return $this->response->collection($this->organizations->getMembers($organization_id),
                                           new UserTransformer, 'users');
    }

    /**
     * Find a member
     *
     * @Get("/{memberId}")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function show(GetMemberRequest $request, $organization_id, $user_id)
    {
        return $this->response->item($this->organizations->getMember($organization_id, $user_id),
                                     new UserTransformer, 'user');
    }

    /**
     * Delete members from an organization
     *
     * @Delete("/{memberId}")
     * @Versions({"v1"})
     * @Request({
     *
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function destroy(DeleteMemberRequest $request, $organization_id, $user_id)
    {
        return $this->response->item($this->organizations->deleteMember($organization_id, $user_id),
                                     new UserTransformer, 'user');
    }

    /**
     * Update organization member
     *
     * @Put("/{memberId}")
     * @Versions({"v1"})
     * @Request({
     *
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function update(UpdateMemberRequest $request, $organization_id, $user_id)
    {
        $member = $this->organizations->updateMember($request->all(), $organization_id, $user_id);
        return $this->response->item($member, new UserTransformer, 'user');
    }


}
