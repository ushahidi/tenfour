<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Http\Requests\Person\GetPersonRequest;
use RollCall\Http\Requests\Person\GetPeopleRequest;
use RollCall\Http\Requests\Person\AddPersonRequest;
use RollCall\Http\Requests\Person\DeletePersonRequest;
use RollCall\Http\Requests\Person\UpdatePersonRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\UserTransformer;
use RollCall\Http\Response;

/**
 * @Resource("People", uri="/api/v1/organizations/{orgId}/people")
 */
class PersonController extends ApiController
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
    public function store(AddPersonRequest $request, $organization_id)
    {
        return $this->response->item($this->organizations->addMember($request->all(), $organization_id),
                                     new UserTransformer, 'person');
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
    public function index(GetPersonRequest $request, $organization_id)
    {
        return $this->response->collection($this->organizations->getMembers($organization_id),
                                           new UserTransformer, 'people');
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
    public function show(GetPersonRequest $request, $organization_id, $person_id)
    {
        return $this->response->item($this->organizations->getMember($organization_id, $person_id),
                                     new UserTransformer, 'person');
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
    public function destroy(DeletePersonRequest $request, $organization_id, $person_id)
    {
        return $this->response->item($this->organizations->deleteMember($organization_id, $person_id),
                                     new UserTransformer, 'person');
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
    public function update(UpdatePersonRequest $request, $organization_id, $person_id)
    {
        $member = $this->organizations->updateMember($request->all(), $organization_id, $person_id);
        return $this->response->item($member, new UserTransformer, 'person');
    }


}
