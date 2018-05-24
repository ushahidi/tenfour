<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Contracts\Repositories\PersonRepository;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Http\Requests\Person\GetPersonRequest;
use TenFour\Http\Requests\Person\GetPeopleRequest;
use TenFour\Http\Requests\Person\AddPersonRequest;
use TenFour\Http\Requests\Person\DeletePersonRequest;
use TenFour\Http\Requests\Person\UpdatePersonRequest;
use TenFour\Http\Requests\Person\InvitePersonRequest;
use TenFour\Http\Requests\Person\NotifyPersonRequest;
use Dingo\Api\Auth\Auth;
use TenFour\Http\Transformers\UserTransformer;
use TenFour\Models\Organization;
use TenFour\Http\Response;
use TenFour\Jobs\SendInvite;
use TenFour\Notifications\PersonToPerson;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Hash;

/**
 * @Resource("People", uri="/api/v1/organizations")
 */
class PersonController extends ApiController
{
    use DispatchesJobs;

    public function __construct(PersonRepository $people, OrganizationRepository $organizations, Auth $auth, Response $response)
    {
        $this->people = $people;
        $this->organizations = $organizations;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Add member to an organization
     *
     * @Post("/{org_id}/people")
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id")
     * })
     *
     * @Versions({"v1"})
     * @Request({
     *       "name": "Testing Testing",
     *       "email": "test@ushahidi.com",
     *       "password": "newpassword",
     *       "password_confirm": "newpassword",
     *       "person_type": "user",
     *       "role": "member"
     *  }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "person": {
     *         "config_profile_reviewed": true,
     *         "config_self_test_sent": false,
     *         "id": 4,
     *         "initials": "TT",
     *         "name": "Testing Testing",
     *         "organization_id": 2,
     *         "person_type": "user",
     *         "role": "member",
     *         "uri": "/users/4"
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function store(AddPersonRequest $request, $organization_id)
    {
        return $this->response->item($this->people->create($organization_id, $request->all()),
                                     new UserTransformer, 'person');
    }

    /**
     * List members of an organization
     *
     * @Get("/{org_id}/people/{?offset,limit}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("offset", type="number", default=0),
     *   @Parameter("limit", type="number", default=0)
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "people": {
     *         {
     *             "contacts": {
     *                 {
     *                     "contact": "linda@ushahidi.com",
     *                     "id": 3,
     *                     "type": "email",
     *                     "uri": "/contact/3",
     *                     "user": {
     *                         "gravatar": "eac7707b1d94e619add99353b2977b6f"
     *                     }
     *                 },
     *                 {
     *                     "contact": "admin@ushahidi.com",
     *                     "id": 5,
     *                     "type": "email",
     *                     "uri": "/contact/5",
     *                     "user": {
     *                         "gravatar": "3578216d69634e299351bb18b7c7fc46"
     *                     }
     *                 }
     *             },
     *             "description": "Admin user",
     *             "id": 2,
     *             "name": "Admin user",
     *             "organization_id": 2,
     *             "person_type": "user",
     *             "profile_picture": null,
     *             "role": "admin",
     *             "uri": "/users/2"
     *         },
     *         {
     *             "contacts": {
     *                 {
     *                     "contact": "org_admin@ushahidi.com",
     *                     "id": 8,
     *                     "type": "email",
     *                     "uri": "/contact/8",
     *                     "user": {
     *                         "gravatar": "a50a84475f1d038b516be6fc5923296d"
     *                     }
     *                 }
     *             },
     *             "description": "Org admin",
     *             "id": 5,
     *             "name": "Org admin",
     *             "organization_id": 2,
     *             "person_type": "user",
     *             "profile_picture": null,
     *             "role": "admin",
     *             "uri": "/users/5"
     *         }
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function index(GetPersonRequest $request, $organization_id)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);

        return $this->response->collection($this->people->all($organization_id, $offset, $limit),
                                           new UserTransformer, 'people');
    }

    /**
     * Find a member
     *
     * @Get("{org_id}/people/{person_id}/{?history_offset,history_limit}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id"),
     *   @Parameter("history_offset", type="number", default=0),
     *   @Parameter("history_limit", type="number", default=1)
     * })
     *
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "person": {
     *         "checkins": {},
     *         "replies": {},
     *         "contacts": {
     *             {
     *                 "contact": "+254721674180",
     *                 "id": 1,
     *                 "type": "phone",
     *                 "updated_at": null,
     *                 "uri": "/contact/1"
     *             },
     *             {
     *                 "contact": "test@ushahidi.com",
     *                 "id": 2,
     *                 "type": "email",
     *                 "updated_at": null,
     *                 "uri": "/contact/2"
     *             }
     *         },
     *         "description": "Test user",
     *         "gravatar": "7563a30b6c2f8bd7eaac8adc38b8de72",
     *         "id": 1,
     *         "initials": "TU",
     *         "name": "Test user",
     *         "organization_id": 2,
     *         "person_type": "user",
     *         "profile_picture": null,
     *         "role": "member",
     *         "uri": "/users/1"
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function show(GetPersonRequest $request, $organization_id, $person_id)
    {
        if ($person_id === 'me') {
            $person_id = $this->auth->user()['id'];
        }

        $history_offset = $request->input('history_offset', 0);
        $history_limit = $request->input('history_limit', 1);

        return $this->response->item($this->people->find($organization_id, $person_id, $history_offset, $history_limit),
                                     new UserTransformer, 'person');
    }

    /**
     * Delete member from an organization
     *
     * @Delete("{org_id}/people/{person_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id")
     * })
     *
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "person": {
     *         "description": "Org member",
     *         "id": 3,
     *         "initials": "OM",
     *         "name": "Org member",
     *         "organization_id": 2,
     *         "person_type": "user",
     *         "profile_picture": null,
     *         "role": "member",
     *         "updated_at": null,
     *         "uri": "/users/3"
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function destroy(DeletePersonRequest $request, $organization_id, $person_id)
    {
        if ($person_id === 'me') {
            $person_id = $this->auth->user()['id'];
        }

        return $this->response->item($this->people->delete($organization_id, $person_id),
                                     new UserTransformer, 'person');
    }

    /**
     * Update organization member
     *
     * @Put("{org_id}/people/{person_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id")
     * })
     *
     * @Request({
     *     "name": "Updated org member",
     *     "password": "newpassword",
     *     "person_type": "user"
     *   }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "person": {
     *         "id": 1,
     *         "initials": "UOM",
     *         "invite_sent": 0,
     *         "name": "Updated org member",
     *         "organization_id": 2,
     *         "person_type": "user",
     *         "profile_picture": null,
     *         "role": "member",
     *         "updated_at": "2017-03-20 08:38:27",
     *         "uri": "/users/1"
     *     }
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function update(UpdatePersonRequest $request, $organization_id, $person_id)
    {
        $member = $this->people->update($organization_id, $request->all(), $person_id);
        return $this->response->item($member, new UserTransformer, 'person');
    }

    /**
     * Invite a member
     *
     * @Post("{org_id}/people/{person_id}/invite")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id")
     * })
     *
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "person": {
     *         "name": "User Name",
     *         "role": "member",
     *         "person_type": "user"
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function invitePerson(InvitePersonRequest $request, $organization_id, $user_id)
    {
        $member = $this->people->find($organization_id, $user_id);
        $organization = $this->organizations->find($organization_id);

        // Queue invite
        $member['invite_token'] = Hash::Make(config('app.key'));
        $member['invite_sent'] = true;

        $this->people->update($organization_id, $member, $user_id);
        $this->dispatch((new SendInvite($member, $organization))/*->onQueue('mails')*/);

        // Return up to date Member
        return $this->response->item($member, new UserTransformer, 'person');
    }

    /**
     * Notify the organization owner
     *
     * @Post("{org_id}/people/owner/notify")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     * })
     *
     * @Request({
     *     "message": "The organization has no credits remaining",
     *   }, headers={"Authorization": "Bearer token"})
     * @Response(200, body="OK")
     *
     * @param Request $request
     * @return Response
     */
    public function notifyOwner(NotifyPersonRequest $request, $organization_id)
    {
        $organization = Organization::where('id', $organization_id)->firstOrFail();
        $message = $request->input('message');
        $from = $this->auth->user();

        $organization->owner()->notify(new PersonToPerson($organization, $from, $message));

        return response("OK", 200);
    }
}
