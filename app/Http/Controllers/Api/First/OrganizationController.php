<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Http\Requests\Organization\GetOrganizationsRequest;
use RollCall\Http\Requests\Organization\CreateOrganizationRequest;
use RollCall\Http\Requests\Organization\GetOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateOrganizationRequest;
use RollCall\Http\Requests\Organization\AddMemberRequest;
use RollCall\Http\Requests\Organization\AddContactRequest;
use RollCall\Http\Requests\Organization\DeleteContactRequest;
use RollCall\Http\Requests\Organization\UpdateContactRequest;
use RollCall\Http\Requests\Organization\DeleteMemberRequest;
use RollCall\Http\Requests\Organization\DeleteOrganizationRequest;
use RollCall\Http\Requests\Organization\UpdateMemberRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\OrganizationTransformer;
use RollCall\Http\Transformers\UserTransformer;
use RollCall\Http\Transformers\ContactTransformer;
//use RollCall\Http\Transformers\RollCallTransformer;
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
        } else {
            $user_id = $request->query('user');
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
     * Add member to an organization
     *
     * @param Request $request
     * @return Response
     */
    public function addMember(AddMemberRequest $request, $organization_id)
    {
        return $this->response->item($this->organizations->addMember($request->all(), $organization_id),
                                     new UserTransformer, 'user');
    }

    /**
     * Add member contact
     *
     * @param Request $request
     * @return Response
     */
    public function addContact(AddContactRequest $request, $organization_id, $user_id)
    {
        return $this->response->item($this->organizations->addContact($request->all(), $organization_id, $user_id),
                                     new ContactTransformer, 'contact');
    }

    /**
     * Update member contact
     *
     * @param Request $request
     * @return Response
     */
    public function updateContact(UpdateContactRequest $request, $organization_id, $user_id, $contact_id)
    {
        return $this->response->item($this->organizations->updateContact($request->all(), $organization_id, $user_id, $contact_id),
                                     new ContactTransformer, 'contact');
    }

    /**
     * Delete member contact
     *
     * @param Request $request
     * @return Response
     */
    public function deleteContact(DeleteContactRequest $request, $organization_id, $user_id, $contact_id)
    {
        return $this->response->item($this->organizations->deleteContact($organization_id, $user_id, $contact_id),
                                     new ContactTransformer, 'contact');
    }

    /**
     * List members of an organization
     *
     * @param Request $request
     * @return Response
     */
    public function listMembers(GetOrganizationRequest $request, $organization_id)
    {
        return $this->response->item($this->organizations->getMembers($organization_id),
                                           new OrganizationTransformer, 'organization');
    }

    /**
     * Find a member
     *
     * @param Request $request
     * @return Response
     */
    public function findMember(GetOrganizationRequest $request, $organization_id, $user_id)
    {
        return $this->response->item($this->organizations->getMember($organization_id, $user_id),
                                     new UserTransformer, 'user');
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
                                     new UserTransformer, 'user');
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
    public function updateMember(UpdateMemberRequest $request, $organization_id, $user_id)
    {
        $member = $this->organizations->updateMember($request->all(), $organization_id, $user_id);
        return $this->response->item($member, new UserTransformer, 'user');
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
