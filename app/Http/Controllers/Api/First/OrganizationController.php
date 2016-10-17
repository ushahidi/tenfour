<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Http\Requests\Organization\GetOrganizationsRequest;
use RollCall\Http\Requests\Organization\CreateOrganizationRequest;
use RollCall\Http\Requests\Organization\GetOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateOrganizationRequest;
use RollCall\Http\Requests\Organization\AddMembersRequest;
use RollCall\Http\Requests\Organization\DeleteMemberRequest;
use RollCall\Http\Requests\Organization\DeleteOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateOrganizationMemberRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\OrganizationTransformer;
use RollCall\Http\Response;

class OrganizationController extends ApiController
{
    public function __construct(OrganizationRepository $organizations, Auth $auth, Response $response)
    {
        $this->organizations = $organizations;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Get all organizations
     *
     * @param Request $request
     * @return Response
     */
    public function all(GetOrganizationsRequest $request)
    {
        $user_id = null;

        if ($request->query('user') === 'me') {
            $user_id = $this->auth->user()['id'];
        } else if ($request->query('user_id')) {
            $user_id = $request->query('user_id');
        }

        if ($user_id) {
            $organizations = $this->organizations->filterByUserId($user_id);
        } else {
            $organizations = $this->organizations->all();
        }

        return $this->response->collection($organizations, new OrganizationTransformer, 'organizations');
    }

    /**
     * Create an organization
     *
     * @param Request $request
     * @return Response
     */
    public function create(CreateOrganizationRequest $request)
    {
        $organization = $this->organizations->create([
                 'name'    => $request->input('name'),
                 'url'     => $request->input('url'),
                 'user_id' => $request->input('user', $this->auth->user()['id']),
        ]);

        return $this->response->item($organization, new OrganizationTransformer, 'organization');
    }

    /**
     * Add members to an organization
     *
     * @param Request $request
     * @return Response
     */
    public function addMembers(AddMembersRequest $request, $organization_id)
    {
        return $this->response->item($this->organizations->addMembers($request->all(), $organization_id),
                                     new OrganizationTransformer, 'organization');
    }

    /**
     * List members of an organization
     *
     * @param Request $request
     * @return Response
     */
    public function listMembers(GetOrganizationRequest $request, $organization_id)
    {
        return $this->response->item($this->organizations->listMembers($organization_id),
                                           new OrganizationTransformer, 'organization');
    }

    /**
     * Delete members from an organization
     *
     * @param Request $request
     * @return Response
     */
    public function deleteMember(DeleteMemberRequest $request, $organization_id, $user_id)
    {
        return $this->response->item($this->organizations->deleteMember($organization_id, $user_id),
                                     new OrganizationTransformer, 'organization');
    }

    /**
     * Get a single organization
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function find(GetOrganizationRequest $request, $organization_id)
    {
        $organization = $this->organizations->find($organization_id);
        return $this->response->item($organization, new OrganizationTransformer, 'organization');
    }

    /**
     * Update organization details
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function update(UpdateOrganizationRequest $request, $organization_id)
    {
        $organization = $this->organizations->update($request->all(), $organization_id);
        return $this->response->item($organization, new OrganizationTransformer, 'organization');
    }

    /**
     * Update organization member
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function updateMember(UpdateOrganizationMemberRequest $request, $organization_id, $user_id)
    {
        $organization = $this->organizations->updateMember($request->all(), $organization_id, $user_id);
        return $this->response->item($organization, new OrganizationTransformer, 'organization');
    }

    /**
     * Delete an organization
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function delete(DeleteOrganizationRequest $request, $organization_id)
    {
        $organization = $this->organizations->delete($organization_id);
        return $this->response->item($organization, new OrganizationTransformer, 'organization');
    }
}
