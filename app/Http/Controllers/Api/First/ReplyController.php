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
 * @Resource("Replies", uri="/api/v1/rollcalls")
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
     * @Get("/{roll_call_id}/replies/{reply_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("roll_call_id", type="number", required=true, description="RollCall id"),
     *   @Parameter("reply_id", type="number", required=true, description="Reply id")
     * })
     *
     * @Request(headers={"Authorization": "Bearer token"})
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
     * @todo Consider merging this with `addReply` and turning it into a resource
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
     * @Post("/{roll_call_id}/replies/")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("roll_call_id", type="number", required=true, description="RollCall id")
     * })
     *
     * @Request({
     *     "answer": "yes",
     *     "message": "I am OK"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "reply": {
     *         "answer": "yes",
     *         "created_at": "2016-03-15 20:27:54",
     *         "id": 6,
     *         "message": "I am OK",
     *         "rollcall": {
     *             "id": 1,
     *             "uri": "/rollcalls/1"
     *         },
     *         "updated_at": "2016-03-15 20:27:54",
     *         "uri": "/rollcalls/1/reply/6",
     *         "user": {
     *             "id": 5,
     *             "uri": "/users/5"
     *         },
     *         "user_id": 5
     *     }
     * })
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

    public function addReplyFromToken(AddReplyRequest $request, $id)
    {
        $user_id = $this->auth->user()['id'];

        $user_id = $this->roll_calls->getUserFromReplyToken($request->get('token'));

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
     * @Get("/{roll_call_id}/replies/")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("roll_call_id", type="number", required=true, description="RollCall id")
     * })
     *
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "replies": {
     *         {
     *             "answer": null,
     *             "contact": {
     *                 "id": 1,
     *                 "uri": "/contacts/1"
     *             },
     *             "created_at": "2017-03-16 10:41:11",
     *             "id": 1,
     *             "location_text": null,
     *             "message": "I am OK",
     *             "message_id": null,
     *             "rollcall": {
     *                 "id": 1,
     *                 "uri": "/rollcalls/1"
     *             },
     *             "updated_at": null,
     *             "uri": "/rollcalls/1/reply/1",
     *             "user": {
     *                 "config_profile_reviewed": 0,
     *                 "config_self_test_sent": 0,
     *                 "created_at": null,
     *                 "description": "Test user",
     *                 "first_time_login": 1,
     *                 "id": 1,
     *                 "initials": "TU",
     *                 "invite_sent": 0,
     *                 "name": "Test user",
     *                 "organization_id": 2,
     *                 "person_type": "user",
     *                 "profile_picture": null,
     *                 "role": "member",
     *                 "updated_at": null,
     *                 "uri": "/users/1"
     *             },
     *         },
     *         {
     *             "answer": null,
     *             "contact": {
     *                 "id": 4,
     *                 "uri": "/contacts/4"
     *             },
     *             "created_at": "2017-03-16 10:41:11",
     *             "id": 3,
     *             "location_text": null,
     *             "message": "Latest answer",
     *             "message_id": null,
     *             "rollcall": {
     *                 "id": 1,
     *                 "uri": "/rollcalls/1"
     *             },
     *             "updated_at": null,
     *             "uri": "/rollcalls/1/reply/3",
     *             "user": {
     *                 "config_profile_reviewed": 0,
     *                 "config_self_test_sent": 0,
     *                 "created_at": null,
     *                 "description": "Org owner",
     *                 "first_time_login": 1,
     *                 "id": 4,
     *                 "initials": "OO",
     *                 "invite_sent": 0,
     *                 "name": "Org owner",
     *                 "organization_id": 2,
     *                 "person_type": "user",
     *                 "profile_picture": null,
     *                 "role": "owner",
     *                 "updated_at": null,
     *                 "uri": "/users/4"
     *             },
     *         }
     *     }
     * })
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
