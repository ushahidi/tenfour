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
use RollCall\Http\Requests\Person\AcceptInviteRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\OrganizationTransformer;
use RollCall\Http\Transformers\UserTransformer;
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

        $location = geoip()->getLocation();

        // Email and SMS is enabled for all new accounts by default
        $settings = [
            'channels' => [
                'email' => [
                    'enabled' => true
                ],
                'sms' => [
                    'enabled' => true,
                    'default_region' => $location->iso_code,
                    'regions' => []
                ]
            ]
        ];

        // Get organization params
        $org_input = [
            'name'      => $input['name'],
            'subdomain' => strtolower($input['subdomain']),
            'settings'  => $settings,
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
     * @Get("/{org_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id")
     * })
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
     * @Put("/{org_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id")
     * })
     * @Request({
     *     "name": "Ushahidi",
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
        $organization = $this->organizations->update($request->except('subdomain'), $organization_id);
        return $this->response->item($organization, new OrganizationTransformer, 'organization');
    }

    /**
     * Delete an organization
     *
     * @Delete("/{org_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id")
     * })
     * @Request({}, headers={"Authorization": "Bearer token"})
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

     /**
     * Accept member invite
     *
     * @Post("invite/{org_id}/accept/{person_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id")
     * })
     *
     * @Request({
     *     "invite_token": "aSecretToken",
     *     "password": "newpassword",
     *     "password_confirm": "newpassword"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "person": {
     *         "name": "User Name",
     *         "role": "member",
     *         "person_type": "user"
     *     }
     * })
     *
     * @todo turn this into a person resource
     *
     * @param InviteMemberRequest $request
     * @return Response
     */
    public function acceptInvite(AcceptInviteRequest $request, $organization_id, $person_id)
    {
        $member = $this->people->find($organization_id, $person_id);

        if ($this->people->testMemberInviteToken($member['id'], $request['invite_token'])) {
            $member['password'] = $request['password'];
            $member['person_type'] = 'user';

            if ($member['role'] !== 'admin') {
                $member['role'] = 'responder';
            }

            $member['invite_token'] = null;
            $member = $this->people->update($organization_id, $member, $person_id);

            return $this->response->item($member, new UserTransformer, 'person');
        }

        abort(401, 'Not authenticated');
    }
}
