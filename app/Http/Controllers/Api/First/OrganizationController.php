<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Http\Requests\Organization\GetOrganizationsRequest;
use RollCall\Http\Requests\Organization\CreateOrganizationRequest;
use RollCall\Http\Requests\Organization\GetOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateOrganizationRequest;
use RollCall\Http\Requests\Organization\DeleteOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateMemberRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\OrganizationTransformer;
use RollCall\Http\Response;

/**
 * @Resource("Organizations", uri="/api/v1/organizations")
 */
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
     * @Get("/")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "organizations": {{
     *         "name": "Ushahidi",
     *         "subdomain": "ushahidi@rollcall.io"
     *     }}
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function index(GetOrganizationsRequest $request)
    {
        // Pass current user ID to repo
        $this->organizations->setCurrentUserId($this->auth->user()['id']);

        $organizations = $this->organizations->all($request->query('subdomain'));

        return $this->response->collection($organizations, new OrganizationTransformer, 'organizations');
    }

    /**
     * Create an organization
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Request({
     *     "name": "Ushahidi",
     *     "subdomain": "ushahidi@rollcall.io"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "organization": {
     *         "name": "Ushahidi",
     *         "subdomain": "ushahidi@rollcall.io"
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function store(CreateOrganizationRequest $request)
    {
        $organization = $this->organizations->create([
                 'name'         => $request->input('name'),
                 'subdomain'    => $request->input('subdomain'),
                 'user_id'      => $request->input('user', $this->auth->user()['id']),
        ]);

        return $this->response->item($organization, new OrganizationTransformer, 'organization');
    }

    /**
     * Get a single organization
     *
     * @Get("/{orgId}")
     * @Versions({"v1"})
     * @Request({}, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "organization": {
     *         "id": 3,
     *         "name": "Ushahidi",
     *         "subdomain": "ushahidi@rollcall.io"
     *     }
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function show(GetOrganizationRequest $request, $organization_id)
    {
        $organization = $this->organizations->find($organization_id);
        return $this->response->item($organization, new OrganizationTransformer, 'organization');
    }

    /**
     * Update organization details
     *
     * @Put("/{orgId}")
     * @Versions({"v1"})
     * @Request({
     *     "name": "Ushahidi",
     *     "subdomain": "ushahidi@rollcall.io"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "organization": {
     *         "id": 3,
     *         "name": "Ushahidi",
     *         "subdomain": "ushahidi"
     *     }
     * })
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
     * @Delete("/{orgId}")
     * @Versions({"v1"})
     * @Request({
     *
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *   "organization": {
     *        "id": 3,
     *        "name": "Ushahidi",
     *        "subdomain": "ushahidi",
     *    }
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function destroy(DeleteOrganizationRequest $request, $organization_id)
    {
        $organization = $this->organizations->delete($organization_id);
        return $this->response->item($organization, new OrganizationTransformer, 'organization');
    }

}
