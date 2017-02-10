<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Http\Requests\Person\Contact\AddContactRequest;
use RollCall\Http\Requests\Person\Contact\DeleteContactRequest;
use RollCall\Http\Requests\Person\Contact\UpdateContactRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\ContactTransformer;
use RollCall\Http\Response;

/**
 * @Resource("Contacts", uri="/api/v1/organizations/{orgId}/people/{personId}/contacts")
 */
class PersonContactController extends ApiController
{
    public function __construct(PersonRepository $people, Auth $auth, Response $response)
    {
        $this->people = $people;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Add member contact
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Request({
            "type": "email",
            "contact": "linda@ushahidi.com",
            "can_receive": "1"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
            "contact": {
                "type": "email",
                "contact": "linda@ushahidi.com",
                "can_receive": "1"
            }
        })
     *
     * @param Request $request
     * @return Response
     */
    public function store(AddContactRequest $request, $organization_id, $user_id)
    {
        return $this->response->item($this->people->addContact($request->all(), $organization_id, $user_id),
                                     new ContactTransformer, 'contact');
    }

    /**
     * Update member contact
     *
     * @Put("/{contactId}")
     * @Versions({"v1"})
     * @Request({
            "type": "email",
            "contact": "linda@ushahidi.com",
            "can_receive": "1"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
            "contact": {
                "type": "email",
                "contact": "linda@ushahidi.com",
                "can_receive": "1"
            }
        })
     *
     * @param Request $request
     * @return Response
     */
    public function update(UpdateContactRequest $request, $organization_id, $user_id, $contact_id)
    {
        return $this->response->item($this->people->updateContact($request->all(), $organization_id, $user_id, $contact_id),
                                     new ContactTransformer, 'contact');
    }

    /**
     * Delete member contact
     *
     * @Delete("/{contactId}")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(201)
     *
     * @param Request $request
     * @return Response
     */
    public function destroy(DeleteContactRequest $request, $organization_id, $user_id, $contact_id)
    {
        return $this->response->item($this->people->deleteContact($organization_id, $user_id, $contact_id),
                                     new ContactTransformer, 'contact');
    }

}
