<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\ReplyRepository;
use RollCall\Http\Requests\Reply\GetReplyRequest;
use RollCall\Http\Requests\Reply\CreateReplyRequest;
use RollCall\Http\Requests\Reply\UpdateReplyRequest;
use RollCall\Http\Transformers\ReplyTransformer;
use RollCall\Http\Response;
use Dingo\Api\Auth\Auth;

class ReplyController extends ApiController
{
    public function __construct(replyRepository $reply, Auth $auth, Response $response)
    {
        $this->reply = $reply;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * Get a single reply
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function find(GetReplyRequest $request, $roll_call_id, $reply_id)
    {
        $reply= $this->reply->find($reply_id);
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
        $reply = $this->reply->create([
            'message'          => $request->input('message'),
            'location_text'    => $request->input('location_text'),
            'answer'           => $request->input('answer'),
            'user_id'          => $this->auth->user()['id']
        ]);

        return $this->response->item($reply, new ReplyTransformer, 'reply');
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
