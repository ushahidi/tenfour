<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Http\Requests\Person\Contact\AddContactRequest;
use RollCall\Http\Requests\Person\Contact\DeleteContactRequest;
use RollCall\Http\Requests\Person\Contact\UpdateContactRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\ContactTransformer;
use RollCall\Http\Response;

/**
 * @Resource("Organizations", uri="/api/v1/organizations/{orgId}/people/{personId}/contacts")
 */
class PersonContactController extends ApiController
{
    public function __construct(OrganizationRepository $organizations, Auth $auth, Response $response)
    {
        $this->organizations = $organizations;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Add member contact
     *
     * @Post("/{orgId}/members/{personId}/contacts")
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
    public function store(AddContactRequest $request, $organization_id, $user_id)
    {
        return $this->response->item($this->organizations->addContact($request->all(), $organization_id, $user_id),
                                     new ContactTransformer, 'contact');
    }

    /**
     * Update member contact
     *
     * @Put("/{orgId}/members/{personId}/contacts/{contactId}")
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
    public function update(UpdateContactRequest $request, $organization_id, $user_id, $contact_id)
    {
        return $this->response->item($this->organizations->updateContact($request->all(), $organization_id, $user_id, $contact_id),
                                     new ContactTransformer, 'contact');
    }

    /**
     * Delete member contact
     *
     * @Delete("/{orgId}/members/{personId}/contacts/{contactId}")
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
    public function destroy(DeleteContactRequest $request, $organization_id, $user_id, $contact_id)
    {
        return $this->response->item($this->organizations->deleteContact($organization_id, $user_id, $contact_id),
                                     new ContactTransformer, 'contact');
    }

}
