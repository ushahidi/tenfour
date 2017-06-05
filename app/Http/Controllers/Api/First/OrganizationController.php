<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Http\Requests\Organization\GetOrganizationsRequest;
use RollCall\Http\Requests\Organization\CreateOrganizationRequest;
use RollCall\Http\Requests\Organization\GetOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateOrganizationRequest;
use RollCall\Http\Requests\Organization\DeleteOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateMemberRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\OrganizationTransformer;
use RollCall\Http\Response;
use RollCall\Services\CreditService;
use DB;

/**
 * @Resource("Organizations", uri="/api/v1/organizations")
 */
class OrganizationController extends ApiController
{

    public function __construct(OrganizationRepository $organizations, PersonRepository $people, ContactRepository $contacts, Auth $auth, Response $response, CreditService $creditService)
    {
        $this->organizations = $organizations;
        $this->people = $people;
        $this->contacts = $contacts;
        $this->auth = $auth;
        $this->response = $response;
        $this->creditService = $creditService;
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
        if ($this->auth->user() && !isset($this->auth->user()->id)) {
            \Log::error('User is authenticated but has no user id');
            return response('No user id', 403);
        }

        // Pass current user ID to repo if the user is a member of an organization
        if (isset($this->auth->user()->organization_id)) {
            $this->organizations->setCurrentUserId($this->auth->user()->id);
        }

        $organizations = $this->organizations->all($request->query('subdomain'), $request->query('name'));

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
     *
     * @return Response
     */
    public function store(CreateOrganizationRequest $request)
    {
        $input = $request->input();
        $organization = [];
        $owner = [];
        $contact = [];

        // Email and SMS is enabled for all new accounts by default
        $settings = [
            'channels' => [
                'email' => [
                    'enabled' => true
                ],
                'sms' => [
                    'enabled' => true
                ]
            ]
        ];

        // Get organization params
        $org_input = [
            'name'      => $input['name'],
            'subdomain' => strtolower($input['subdomain']),
            'settings'  => $settings,
            'paid_until'=> DB::raw('NOW()')
        ];

        // Get owner details
        $owner_input = [
            'name'     => $input['owner'],
            'role'     => 'owner',
            'password' => $input['password'],
        ];

        $contact_input = [
            'contact' => $input['email'],
            'type'    => 'email'
        ];

        DB::transaction(function () use ($org_input, $owner_input, $contact_input, &$organization, &$owner, &$contact) {
            $organization = $this->organizations->create($org_input);

            $this->creditService->createStartingBalance($organization['id']);

            $owner = $this->people->create($organization['id'], $owner_input);

            $contact = $this->contacts->create($contact_input + [
                'user_id'         => $owner['id'],
                'organization_id' => $organization['id'],
            ]);
        });

        $result = $organization + [
            'user' => $owner + [
                'contact' => $contact
            ]
        ];

        return $this->response->item($result, new OrganizationTransformer, 'organization');
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
