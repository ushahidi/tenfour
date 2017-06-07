<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Http\Requests\Person\Contact\AddContactRequest;
use RollCall\Http\Requests\Person\Contact\DeleteContactRequest;
use RollCall\Http\Requests\Person\Contact\UpdateContactRequest;
use Dingo\Api\Auth\Auth;
use RollCall\Http\Transformers\ContactTransformer;
use RollCall\Http\Response;
use Illuminate\Http\Request;

/**
 * @Resource("Contacts", uri="/api/v1/organizations")
 */
class PersonContactController extends ApiController
{
    public function __construct(PersonRepository $people, ContactRepository $contact, Auth $auth, Response $response)
    {
        $this->people = $people;
        $this->contact = $contact;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Add member contact
     *
     * @Post("/{org_id}/people/{person_id}/contacts")
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id")
     * })
     *
     * @Versions({"v1"})
     * @Request({
     *       "type": "email",
     *       "contact": "linda@ushahidi.com",
     *       "preferred": "1"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *       "contact": {
     *           "type": "email",
     *           "contact": "linda@ushahidi.com",
     *           "preferred": "1"
     *       }
     *   })
     *
     * @param Request $request
     * @return Response
     */
    public function store(AddContactRequest $request, $organization_id, $user_id)
    {
        return $this->response->item($this->people->addContact($organization_id, $user_id, $request->all()),
                                     new ContactTransformer, 'contact');
    }

    /**
     * Update member contact
     *
     * @Put("{org_id}/people/{person_id}/contacts/{contact_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id"),
     *   @Parameter("contact_id", type="number", required=true, description="Contact id")
     * })
     *
     * @Request({
     *       "type": "email",
     *       "contact": "linda@ushahidi.com",
     *       "preferred": "1"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *       "contact": {
     *           "type": "email",
     *           "contact": "linda@ushahidi.com",
     *           "preferred": "1"
     *       }
     *   })
     *
     * @param Request $request
     * @return Response
     */
    public function update(UpdateContactRequest $request, $organization_id, $user_id, $contact_id)
    {
        return $this->response->item($this->people->updateContact($organization_id, $user_id, $request->all(), $contact_id),
                                     new ContactTransformer, 'contact');
    }

    /**
     * Delete member contact
     *
     * @Delete("{org_id}/people/{person_id}/contacts/{contact_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id"),
     *   @Parameter("contact_id", type="number", required=true, description="Contact id")
     * })
     *
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "contact": {
     *         "type": "email",
     *         "contact": "linda@ushahidi.com",
     *         "preferred": "1"
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function destroy(DeleteContactRequest $request, $organization_id, $user_id, $contact_id)
    {
        return $this->response->item($this->people->deleteContact($organization_id, $user_id, $contact_id),
                                     new ContactTransformer, 'contact');
    }

    /**
     * Unsubscribe an email address from RollCalls
     *
     * @Post("/unsubscribe")
     * @Versions({"v1"})
     * @Request({
     *     "token": "anunsubscribetoken",
     *     "email": "linda@ushahidi.com",
     * })
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function unsubscribe(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
        ]);

        $this->contact->unsubscribe($request['token']);

        // public API method, don't expose anything in the response

        return response('OK', 200);
    }
}
