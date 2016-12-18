<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Http\Requests\RollCall\GetRollCallsRequest;
use RollCall\Http\Requests\RollCall\GetRollCallRequest;
use RollCall\Http\Requests\RollCall\CreateRollCallRequest;
use RollCall\Http\Requests\RollCall\UpdateRollCallRequest;
use RollCall\Http\Requests\RollCall\AddContactsRequest;
use RollCall\Http\Requests\RollCall\AddReplyRequest;
use RollCall\Http\Requests\RollCall\GetReplyRequest;
use RollCall\Http\Transformers\RollCallTransformer;
use RollCall\Http\Transformers\ContactTransformer;
use RollCall\Http\Transformers\ReplyTransformer;
use RollCall\Http\Transformers\UserTransformer;
use RollCall\Http\Response;
use Dingo\Api\Auth\Auth;

use RollCall\Jobs\SendRollCall;

class RollCallController extends ApiController
{
    public function __construct(RollCallRepository $roll_calls, Auth $auth, Response $response)
    {
        $this->roll_calls = $roll_calls;
        $this->auth = $auth;
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
        $user_id = null;

        if ($request->query('user') === 'me') {
            $user_id = $this->auth->user()['id'];
        } else {
            $user_id = $request->query('user');
        }

        $roll_calls = $this->roll_calls->all($request->query('organization'), $user_id);

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
        $roll_call = $this->roll_calls->create($request->input() + [
            'user_id' => $this->auth->user()['id'],
        ]);

        // Send roll call
        dispatch(new SendRollCall($roll_call));

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

        // Send roll call to new recipients
        if ($request->input('recipients')) {
            $roll_call_to_dispatch = $roll_call;
            $roll_call_to_dispatch['recipients'] = $request->input('recipients');

            dispatch(new SendRollCall($roll_call_to_dispatch));
        }

        return $this->response->item($roll_call, new RollCallTransformer, 'rollcall');
    }

    /**
     * List roll call recipients
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listRecipients(GetRollCallRequest $request, $id)
    {
        return $this->response->collection($this->roll_calls->getRecipients($id, $request->query('unresponsive')),
                                     new UserTransformer, 'recipients');
    }

    /**
     * List roll call messages
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listMessages(GetRollCallRequest $request, $id)
    {
        return $this->response->collection($this->roll_calls->getMessages($id),
                                     new ContactTransformer, 'messages');
    }

    /**
     * Delete a roll call
     */
    public function delete()
    {
        //
    }
}
