<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Http\Requests\RollCall\GetRollCallsRequest;
use RollCall\Http\Requests\RollCall\GetRollCallRequest;
use RollCall\Http\Requests\RollCall\CreateRollCallRequest;
use RollCall\Http\Requests\RollCall\UpdateRollCallRequest;

use RollCall\Http\Transformers\RollCallTransformer;
use RollCall\Http\Response;

class RollCallController extends ApiController
{
    public function __construct(RollCallRepository $rollcalls, Response $response)
    {
        $this->rollcalls = $rollcalls;
        $this->response = $response;
    }

    /**
     * Get all rollcalls for an organization
     *
     * @param Request $request
     * @param org_id
     * @return Response
     */
    public function all(GetRollCallsRequest $request)
    {
        if ($request->query('org_id')) {
            $rollcalls = $this->rollcalls->filterByOrganizationId($request->query('org_id'));
        } else {
            $rollcalls = $this->rollcalls->all();
        }

        return $this->response->collection($rollcalls, new RollCallTransformer, 'rollcalls');
    }

    /**
     * Get a single rollcall
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function find(GetRollCallRequest $request, $id)
    {
        $rollcall = $this->rollcalls->find($id);
        return $this->response->item($rollcall, new RollCallTransformer, 'rollcall');
    }

    /**
     * Create a rollcall
     * @param Request $request
     * @return Response
     *
     */
    public function create(CreateRollCallRequest $request)
    {
        $rollcall = $this->rollcalls->create([
            'message'          => $request->input('message'),
            'contact_id'       => $request->input('contact_id'),
            'organization_id'  => $request->input('organization_id'),
        ]);

        return $this->response->item($rollcall, new RollCallTransformer, 'rollcall');
    }

    /**
     * Update a rollcall
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function update(UpdateRollCallRequest $request, $id)
    {
        $rollcall = $this->rollcalls->update($request->all(), $id);

        return $this->response->item($rollcall, new RollCallTransformer, 'rollcall');
    }

    /**
     * Delete a rollcall
     */
    public function delete()
    {

    }

}
