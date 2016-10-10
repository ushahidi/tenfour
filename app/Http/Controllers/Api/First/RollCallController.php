<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Http\Requests\RollCall\GetRollCallsRequest;
use RollCall\Http\Requests\RollCall\GetRollCallRequest;
use RollCall\Http\Requests\RollCall\CreateRollCallRequest;
use RollCall\Http\Requests\RollCall\UpdateRollCallRequest;
use RollCall\Http\Requests\RollCall\AddContactsRequest;
use RollCall\Http\Requests\RollCall\AddReplyRequest;

use RollCall\Http\Transformers\RollCallTransformer;
use RollCall\Http\Response;

class RollCallController extends ApiController
{
    public function __construct(RollCallRepository $roll_calls, Response $response)
    {
        $this->roll_calls = $roll_calls;
        $this->response = $response;
    }

    /**
     * Get all roll calls for an organization
     *
     * @param Request $request
     * @param org_id
     * @return Response
     */
    public function all(GetRollCallsRequest $request)
    {
        if ($request->query('organization')) {
            $roll_calls = $this->roll_calls->filterByOrganizationId($request->query('organization'));
        } else {
            $roll_calls = $this->roll_calls->all();
        }

        return $this->response->collection($roll_calls, new RollCallTransformer, 'rollcalls');
    }

    /**
     * Get a single roll call
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function find(GetRollCallRequest $request, $id)
    {
        $roll_call = $this->roll_calls->find($id);
        return $this->response->item($roll_call, new RollCallTransformer, 'rollcall');
    }

    /**
     * Create a roll call
     *
     * @param Request $request
     * @return Response
     *
     */
    public function create(CreateRollCallRequest $request)
    {
        $roll_call = $this->roll_calls->create([
            'message'          => $request->input('message'),
            'organization_id'  => $request->input('organization'),
        ]);

        return $this->response->item($roll_call, new RollCallTransformer, 'rollcall');
    }

    /**
     * Update a roll call
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function update(UpdateRollCallRequest $request, $id)
    {
        $roll_call = $this->roll_calls->update($request->all(), $id);

        return $this->response->item($roll_call, new RollCallTransformer, 'rollcall');
    }

    /**
     * Add contacts to a roll call
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addContacts(AddContactsRequest $request, $id)
    {
        $roll_call = $this->roll_calls->addContacts($request->all(), $id);

        return $this->response->item($roll_call, new RollCallTransformer, 'rollcall');
    }

    /**
     * Add reply to a roll call
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addReply(AddReplyRequest $request, $id)
    {
        $roll_call = $this->roll_calls->addReply([
            'message'      => $request->input('message'),
            'contact_id'   => $request->input('contact'),
            'roll_call_id' => $id,
        ], $id);

        return $this->response->item($roll_call, new RollCallTransformer, 'rollcall');
    }

    /**
     * List roll call contacts
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listContacts(GetRollCallRequest $request, $id)
    {
        return $this->response->item($this->roll_calls->getContacts($id),
                                     new RollCallTransformer, 'rollcall');
    }

    /**
     * List roll call replies
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listReplies(GetRollCallRequest $request, $id)
    {
        return $this->response->item($this->roll_calls->getReplies($id),
                                     new RollCallTransformer, 'rollcall');
    }

    /**
     * Delete a roll call
     */
    public function delete()
    {
        //
    }
}
