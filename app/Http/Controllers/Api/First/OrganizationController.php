<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Http\Requests\Organization\GetOrganizationsRequest;
use RollCall\Http\Requests\Organization\CreateOrganizationRequest;
use RollCall\Http\Requests\Organization\GetOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateOrganizationRequest;
use RollCall\Http\Requests\Organization\DeleteOrganizationRequest;
use Dingo\Api\Auth\Auth;


class OrganizationController extends ApiController
{
    public function __construct(OrganizationRepository $organizations, Auth $auth)
    {
        $this->organizations = $organizations;
        $this->auth = $auth;
    }

    /**
     * Get all organization
     *
     * @param Request $request
     * @return Response
     */
    public function all(GetOrganizationsRequest $request)
    {
        $organizations = $this->organizations->all();
        return $organizations;
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
                 'name' => $request->input('name'),
                 'url'  => $request->input('url'),
        ]);

        return $organization;
    }

    /**
     * Get a single organization
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function find(GetOrganizationRequest $request, $id)
    {
        $organization = $this->organizations->find($id);
        return $organization;
    }

    /**
     * Update organization details
     *
     * @param Request $request
     * @param int $id
     * 
     * @return Response
     */
    public function update(UpdateOrganizationRequest $request, $id)
    {
        $organization = $this->organizations->update($request->all(), $id);
        return $organization;
    }

    /**
     * Delete an organization
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function delete(DeleteOrganizationRequest $request, $id)
    {
        $organization = $this->organizations->delete($id);
        return $organization;
    }

}
