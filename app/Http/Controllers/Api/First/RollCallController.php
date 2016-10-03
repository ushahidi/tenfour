<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Http\Requests\RollCall\GetRollCallsRequest;
use RollCall\Http\Requests\RollCall\GetRollCallRequest;
use RollCall\Http\Requests\RollCall\CreateRollCallRequest;
use RollCall\Http\Requests\RollCall\UpdateRollCallRequest;
use RollCall\Http\Requests\RollCall\AddContactsRequest;

use RollCall\Http\Transformers\RollCallTransformer;
use RollCall\Http\Response;

class RollCallController extends ApiController
{
    public function __construct(RollCallRepository $rollCalls, Response $response)
    {
        $this->rollCalls = $rollCalls;
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
            $rollCalls = $this->rollCalls->filterByOrganizationId($request->query('organization'));
        } else {
            $rollCalls = $this->rollCalls->all();
        }

        return $this->response->collection($rollCalls, new RollCallTransformer, 'rollcalls');
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
        $rollCall = $this->rollCalls->find($id);
        return $this->response->item($rollCall, new RollCallTransformer, 'rollcall');
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
        $rollCall = $this->rollCalls->create([
            'message'          => $request->input('message'),
            'organization_id'  => $request->input('organization'),
        ]);

        return $this->response->item($rollCall, new RollCallTransformer, 'rollcall');
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
        $rollCall = $this->rollCalls->update($request->all(), $id);

        return $this->response->item($rollCall, new RollCallTransformer, 'rollcall');
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
        $rollCall = $this->rollCalls->addContacts($request->all(), $id);

        return $this->response->item($rollCall, new RollCallTransformer, 'rollcall');
    }

    /**
     * List roll call contacs
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listContacts(GetRollCallRequest $request, $id)
    {
        return $this->response->item($this->rollCalls->listContacts($id),
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
