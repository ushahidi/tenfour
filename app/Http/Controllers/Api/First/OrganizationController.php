<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Http\Requests\Organization\GetOrganizationsRequest;
use RollCall\Http\Requests\Organization\CreateOrganizationRequest;
use RollCall\Http\Requests\Organization\GetOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateOrganizationRequest;
use RollCall\Http\Requests\Organization\AddMembersRequest;
use RollCall\Http\Requests\Organization\DeleteMembersRequest;
use RollCall\Http\Requests\Organization\DeleteOrganizationRequest;
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
        $user_id = $request->query('user_id');

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
                 'user_id' => $request->input('user_id', $this->auth->user()['id']),
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
     * Delete members from an organization
     *
     * @param Request $request
     * @return Response
     */
    public function deleteMembers(DeleteMembersRequest $request, $organization_id)
    {
        return $this->response->item($this->organizations->deleteMembers($request->all(), $organization_id),
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
