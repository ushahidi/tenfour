<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Http\Requests\Person\GetPersonRequest;
use RollCall\Http\Requests\Person\GetPeopleRequest;
use RollCall\Http\Requests\Person\AddPersonRequest;
use RollCall\Http\Requests\Person\DeletePersonRequest;
use RollCall\Http\Requests\Person\UpdatePersonRequest;
use RollCall\Http\Requests\Person\InvitePersonRequest;
use RollCall\Http\Requests\Person\AcceptInviteRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\UserTransformer;
use RollCall\Http\Response;
use RollCall\Jobs\SendInvite;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Hash;

/**
 * @Resource("People", uri="/api/v1/organizations/{orgId}/people")
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
     * @Post("/")
     * @Versions({"v1"})
     * @Request({
            "name": "Testing Testing",
            "email": "test@ushahidi.com",
            "password": "newpassword",
            "password_confirm": "newpassword"
       }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
            "person": {
                "id": 3,
                "name": "Testing Testing",
                "email": "test@ushahidi.com"
            }
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
     * @Get("/")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
            "people": {{
                "name": "Robbie",
                "email": "robbie@ushahidi.com"
            }}
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function index(GetPersonRequest $request, $organization_id)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);

        return $this->response->collection($this->people->all($organization_id, $offset, $limit),
                                           new UserTransformer, 'people');
    }

    /**
     * Find a member
     *
     * @Get("/{personId}")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
            "person": {
                "id": 3,
                "name": "Testing Testing",
                "email": "test@ushahidi.com",
                "username": "ushahidi",
                "created_at": "2016-03-30 16:11:36",
                "updated_at": "2016-03-30 16:11:36"
            }
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
        return $this->response->item($this->people->find($organization_id, $person_id),
                                     new UserTransformer, 'person');
    }

    /**
     * Delete members from an organization
     *
     * @Delete("/{memberId}")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(201)
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
     * @Put("/{memberId}")
     * @Versions({"v1"})
     * @Request({
            "name": "Testing Testing",
            "email": "test@ushahidi.com",
            "password": "newpassword",
            "password_confirm": "newpassword"
        }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
            "user": {
                "id": 3,
                "name": "Testing Testing",
                "email": "test@ushahidi.com",
                "username": "ushahidi",
                "created_at": "2016-03-30 16:11:36",
                "updated_at": "2016-03-30 16:11:36"
            }
        })
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
     * @Post("/{personId}")
     * @Versions({"v1"})
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
        $this->dispatch(new SendInvite($member, $organization));

        // Return up to date Member
        return $this->response->item($member, new UserTransformer, 'person');
    }

    /**
     * Accept member invite
     *
     * @Post("invite/{organisationId}/accept/{personId}")
     * @Versions({"v1"})
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
     * @param InviteMemberRequest $request
     * @return Response
     */
    public function acceptInvite(AcceptInviteRequest $request, $organization_id, $person_id)
    {
        $member = $this->people->find($organization_id, $person_id);
        if ($this->people->testMemberInviteToken($member['id'], $request['invite_token'])) {
            $member['password'] = $request['password'];
            $member['person_type'] = 'user';
            $member['role'] = 'member';
            $member['invite_token'] = null;
            $member = $this->people->update($organization_id, $member, $person_id);

            return $this->response->item($member, new UserTransformer, 'person');
        }
        abort(401, 'Not authenticated');
    }

}
