<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\ReplyRepository;
use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Http\Requests\Reply\GetReplyRequest;
use RollCall\Http\Requests\Reply\AddReplyRequest;
use RollCall\Http\Requests\Reply\CreateReplyRequest;
use RollCall\Http\Requests\Reply\UpdateReplyRequest;
use RollCall\Http\Transformers\ReplyTransformer;
use RollCall\Http\Response;
use Dingo\Api\Auth\Auth;

/**
 * @Resource("Replies", uri="/api/v1/rollcalls/{rollCallId}/replies")
 */
class ReplyController extends ApiController
{
    public function __construct(replyRepository $reply, RollCallRepository $roll_calls, Auth $auth, Response $response)
    {
        $this->reply = $reply;
        $this->roll_calls = $roll_calls;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Get a single reply
     *
     * @Get("/{replyId}")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token")}
     * @Response(200, body={
     *     "reply": {
     *          "answer": null,
     *          "contact": {
     *               "id": 3,
     *               "uri": "/contacts/3"
     *           },
     *           "created_at": "2016-04-15 20:01:55",
     *           "id": 1,
     *           "location_text": null,
     *           "message": "I am OK",
     *           "message_id": null,
     *           "rollcall": {
     *               "id": 4,
     *               "uri": "/rollcalls/4"
     *           },
     *           "updated_at": null,
     *           "uri": "/rollcalls/4/reply/1",
     *           "user": {
     *               "id": 1,
     *               "uri": "/users/1"
     *           },
     *     }
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function find(GetReplyRequest $request, $roll_call_id, $reply_id)
    {
        $reply = $this->reply->find($reply_id);
        return $this->response->item($reply, new ReplyTransformer, 'reply');
    }

    /**
     * Create a reply
     *
     * @param Request $request
     * @return Response
     *
     */
    public function create(CreateReplyRequest $request)
    {
        $reply = $this->reply->create(
          $request->input() + [
            'user_id' => $this->auth->user()['id']
          ]
        );

        return $this->response->item($reply, new ReplyTransformer, 'reply');
    }

    /**
     * Add reply
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addReply(AddReplyRequest $request, $id)
    {
        $user_id = $this->auth->user()['id'];
        $reply = $this->reply->addReply(
          $request->input() + [
            'user_id' => $user_id,
            'roll_call_id' => $id
          ], $id);

        // Update response status
        $this->roll_calls->updateRecipientStatus($id, $user_id, 'replied');
        return $this->response->item($reply, new ReplyTransformer, 'reply');
    }

    /**
     * List roll call replies
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listReplies(GetReplyRequest $request, $id)
    {
        return $this->response->collection($this->reply->getReplies($id, $request->query('users'), $request->query('contacts')),
                                     new ReplyTransformer, 'replies');
    }

    /**
     * Update a reply
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function update(UpdateReplyRequest $request, $roll_call_id, $reply_id)
    {
        $reply = $this->reply->update($request->all(), $reply_id);

        return $this->response->item($reply, new ReplyTransformer, 'reply');
    }
}
