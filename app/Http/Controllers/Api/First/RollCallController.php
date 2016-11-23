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
use RollCall\Http\Response;
use Dingo\Api\Auth\Auth;

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
        $roll_call = $this->roll_calls->create([
            'message'          => $request->input('message'),
            'organization_id'  => $request->input('organization'),
            'user_id'          => $this->auth->user()['id'],
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
        $input = $request->all();

        if (is_array(head($input))) {
            return $this->response->collection($this->roll_calls->addContacts($input, $id),
                                               new ContactTransformer, 'contacts');
        }

        return $this->response->item($this->roll_calls->addContact($input, $id),
                                     new ContactTransformer, 'contact');
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
        $reply = $this->roll_calls->addReply([
            'message'      => $request->input('message'),
            'contact_id'   => $request->input('contact'),
            'roll_call_id' => $id,
        ], $id);

        return $this->response->item($reply, new ReplyTransformer, 'reply');
    }

    /**
     * Retrieve reply for given roll call
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getReply(GetReplyRequest $request, $id, $replyId)
    {
        $reply = $this->roll_calls->getReply($id, $replyId);
        return $this->response->item($reply, new ReplyTransformer, 'reply');
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
        return $this->response->item($this->roll_calls->getContacts($id, $request->query('unresponsive')),
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
        return $this->response->item($this->roll_calls->getReplies($id, $request->query('contacts')),
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
