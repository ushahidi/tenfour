<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Http\Requests\Person\GetPersonRequest;
use RollCall\Http\Requests\Person\GetPeopleRequest;
use RollCall\Http\Requests\Person\AddPersonRequest;
use RollCall\Http\Requests\Person\DeletePersonRequest;
use RollCall\Http\Requests\Person\UpdatePersonRequest;
use RollCall\Http\Requests\Person\InviteMemberRequest;
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

    public function __construct(PersonRepository $people, Auth $auth, Response $response)
    {
        $this->people = $people;
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
        return $this->response->item($this->people->addMember($request->all(), $organization_id),
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
        return $this->response->collection($this->people->getMembers($organization_id),
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
        return $this->response->item($this->people->getMember($organization_id, $person_id),
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
        return $this->response->item($this->people->deleteMember($organization_id, $person_id),
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
        $role = ['role' => $request->input('role', 'member')];

        $input = array_merge($request->all(), $role);

        $member = $this->people->updateMember($input, $organization_id, $person_id);
        return $this->response->item($member, new UserTransformer, 'person');
    }

    /**
     * Invite a member
     *
     * @param Request $request
     * @return Response
     */
    public function invitePerson(GetPersonRequest $request, $organization_id, $user_id)
    {
        $member = $this->people->getMember($organization_id, $user_id);
        //$organization = $this->organizations->find($organization_id);
        // Queue invite
        $member['invite_token'] = Hash::Make(config('app.key'));
        $member['invite_sent'] = true;

        $this->people->updateMember($member, $organization_id, $user_id);
        $this->dispatch(new SendInvite($member, $organization));

        // Return up to date Member
        return $this->response->item($member, new UserTransformer, 'user');
    }

    /**
     * Accept member invite
     *
     * @Put("invite/{organisationId}/accept/{memberId}")
     * @Versions({"v1"})
     * @Request({
     *     invite_token: "aSecretToken",
     *     password: "newpassword",
     *     password_confirm: "newpassword"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     user: {
     *         name: "User Name",
     *         role: "member",
     *         person_type: "user"
     *     }
     * })
     *
     * @param InviteMemberRequest $request
     * @return Response
     */
    public function acceptInvite(InviteMemberRequest $request, $organization_id, $memberId)
    {
        $member = $this->people->getMember($organization_id, $memberId);
        if ($this->people->testMemberInviteToken($member['id'], $request['invite_token'])) {
            $member['password'] = $request['password'];
            $member['person_type'] = 'user';
            $member['role'] = 'member';
            $member['invite_token'] = null;
            $member = $this->people->updateMember($member, $organization_id, $memberId);

            return $this->response->item($member, new UserTransformer, 'user');
        }
        abort(401, 'Not authenticated');
    }

}
