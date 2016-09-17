<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Http\Requests\Contact\GetContactsRequest;
use RollCall\Http\Requests\Contact\GetContactRequest;
use RollCall\Http\Requests\Contact\CreateContactRequest;
use RollCall\Http\Requests\Contact\UpdateContactRequest;
use RollCall\Http\Requests\Contact\DeleteContactRequest;

use RollCall\Http\Transformers\ContactTransformer;
use RollCall\Http\Response;

class ContactController extends ApiController
{
    public function __construct(ContactRepository $contacts, Response $response)
    {
        $this->contacts = $contacts;
        $this->response = $response;
    }

    /**
     * Get all contacts
     *
     * @param Request $request
     * @return Response
     */
    public function all(GetContactsRequest $request)
    {
        $contacts = $this->contacts->all();

        return $this->response->collection($contacts, new ContactTransformer, 'contacts');
    }

    /**
     * Create a contact
     *
     * @param Request $request
     * @return Response
     */
    public function create(CreateContactRequest $request)
    { 
        $contact = $this->contacts->create([
            'user_id'     => $request->input('user_id'),
            'can_receive' => $request->input('can_receive'),
            'type'        => $request->input('type'),
            'contact'     => $request->input('contact'),
        ]);

        return $this->response->item($contact, new ContactTransformer, 'contact');
    }

    /**
     * Get a single contact
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    /*
    public function find(GetContactRequest $request, $id)
    {
        $contact = $this->contacts->find($id);
        return $contact;

    }
    */

    /**
     * Update a contact
     *
     * @param Request $request
     * @param int $id
     * 
     * @return Response
     */
    public function update(UpdateContactRequest $request, $id)
    {
        $contact = $this->contacts->update($request->all(), $id);
        
        return $this->response->item($contact, new ContactTransformer, 'contact');
    }

    /**
     * Delete a contact
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function delete(DeleteContactRequest $request, $id)
    {
        $contact = $this->contacts->delete($id);
       
        return $this->response->item($contact, new ContactTransformer, 'contact');
    }
}