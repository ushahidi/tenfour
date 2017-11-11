<?php

namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Http\Requests\RollCall\GetRollCallsRequest;
use RollCall\Http\Requests\RollCall\GetRollCallRequest;
use RollCall\Http\Requests\RollCall\CreateRollCallRequest;
use RollCall\Http\Requests\RollCall\UpdateRollCallRequest;
use RollCall\Http\Requests\RollCall\SendRollCallRequest;
use RollCall\Http\Requests\RollCall\AddContactsRequest;
use RollCall\Http\Requests\RollCall\AddReplyRequest;
use RollCall\Http\Requests\RollCall\GetReplyRequest;
use RollCall\Http\Transformers\RollCallTransformer;
use RollCall\Http\Transformers\ContactTransformer;
use RollCall\Http\Transformers\ReplyTransformer;
use RollCall\Http\Transformers\UserTransformer;
use RollCall\Http\Response;
use RollCall\Jobs\SendRollCall;
use RollCall\Services\CreditService;

use Dingo\Api\Auth\Auth;
use App;

/**
 * @Resource("RollCalls", uri="/api/v1/rollcalls")
 */
class RollCallController extends ApiController
{
    public function __construct(RollCallRepository $roll_calls, Auth $auth, Response $response, CreditService $creditService)
    {
        $this->roll_calls = $roll_calls;
        $this->auth = $auth;
        $this->response = $response;
        $this->creditService = $creditService;
    }

    /**
     * Get all roll calls for an organization
     *
     * @Get("/{?offset,limit}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("offset", default=0),
     *     @Parameter("limit", default=0)
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "rollcalls": {
     *         {
     *             "answers": null,
     *             "complaint_count": 0,
     *             "created_at": null,
     *             "id": 1,
     *             "message": "Are you OK?",
     *             "organization": {
     *                 "id": 2,
     *                 "uri": "/organizations/2"
     *             },
     *             "recipients": {
     *                 {
     *                     "id": 1,
     *                     "uri": "/users/1"
     *                 }
     *             },
     *             "replies": {
     *                 {
     *                     "answer": null,
     *                     "contact": {
     *                         "id": 1,
     *                         "uri": "/contacts/1"
     *                     },
     *                     "created_at": "2017-03-17 09:09:01",
     *                     "id": 1,
     *                     "location_text": null,
     *                     "message": "I am OK",
     *                     "message_id": null,
     *                     "rollcall": {
     *                         "id": 1,
     *                         "uri": "/rollcalls/1"
     *                     },
     *                     "updated_at": null,
     *                     "uri": "/rollcalls/1/reply/1",
     *                     "user": {
     *                         "id": 1,
     *                         "uri": "/users/1"
     *                     }
     *                 }
     *             },
     *             "reply_count": 2,
     *             "send_via": null,
     *             "sent": 0,
     *             "sent_count": 4,
     *             "status": "pending",
     *             "updated_at": null,
     *             "uri": "/rollcalls/1",
     *             "user": {
     *                 "id": 4,
     *                 "uri": "/users/4"
     *             }
     *         },
     *         {
     *             "answers": null,
     *             "complaint_count": 0,
     *             "created_at": null,
     *             "id": 3,
     *             "message": "Where is everyone now?",
     *             "organization": {
     *                 "id": 2,
     *                 "uri": "/organizations/2"
     *             },
     *             "recipients": {
     *                 {
     *                     "id": 3,
     *                     "uri": "/users/3"
     *                 }
     *             },
     *             "replies": {
     *                 {
     *                     "answer": null,
     *                     "contact": {
     *                         "id": 6,
     *                         "uri": "/contacts/6"
     *                     },
     *                     "created_at": "2017-03-17 09:08:01",
     *                     "id": 5,
     *                     "location_text": null,
     *                     "message": "Latest answer again",
     *                     "message_id": null,
     *                     "rollcall": {
     *                         "id": 3,
     *                         "uri": "/rollcalls/3"
     *                     },
     *                     "updated_at": null,
     *                     "uri": "/rollcalls/3/reply/5",
     *                     "user": {
     *                         "id": 4,
     *                         "uri": "/users/4"
     *                     }
     *                 }
     *             },
     *             "reply_count": 1,
     *             "send_via": null,
     *             "sent": 0,
     *             "sent_count": 0,
     *             "status": "pending",
     *             "updated_at": null,
     *             "uri": "/rollcalls/3",
     *             "user": {
     *                 "id": 1,
     *                 "uri": "/users/1"
     *             }
     *         },
     *         {
     *             "answers": {
     *                 "yes",
     *                 "no"
     *             },
     *             "complaint_count": 0,
     *             "created_at": null,
     *             "id": 4,
     *             "message": "Could you update with an answer?",
     *             "organization": {
     *                 "id": 2,
     *                 "uri": "/organizations/2"
     *             },
     *             "recipients": {},
     *             "replies": {},
     *             "reply_count": 0,
     *             "send_via": null,
     *             "sent": 0,
     *             "sent_count": 1,
     *             "status": "pending",
     *             "updated_at": null,
     *             "uri": "/rollcalls/4",
     *             "user": {
     *                 "id": 1,
     *                 "uri": "/users/1"
     *             }
     *         }
     *     }
     * })
     *
     * @param Request $request
     * @param org_id
     * @return Response
     */
    public function all(GetRollCallsRequest $request)
    {
        $user_id = null;

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);

        if ($request->query('user') === 'me') {
            $user_id = $this->auth->user()['id'];
        } else {
            $user_id = $request->query('user');
        }

        $roll_calls = $this->roll_calls->all($request->query('organization'), $user_id, $request->input('recipient_id'), $offset, $limit);

        return $this->response->collection($roll_calls, new RollCallTransformer, 'rollcalls');
    }

    /**
     * Get a single roll call
     *
     * @Get("/{roll_call_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("roll_call_id", type="number", required=true, description="Roll Call id")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "rollcall": {
     *         "answers": null,
     *         "complaint_count": 0,
     *         "created_at": null,
     *         "id": 1,
     *         "message": "Are you OK?",
     *         "organization": {
     *             "id": 2,
     *             "uri": "/organizations/2"
     *         },
     *         "recipients": {
     *             {
     *                 "id": 1,
     *                 "uri": "/users/1"
     *             },
     *             {
     *                 "id": 2,
     *                 "uri": "/users/2"
     *             }
     *         },
     *         "replies": {
     *             {
     *                 "answer": null,
     *                 "contact": {
     *                     "id": 1,
     *                     "uri": "/contacts/1"
     *                 },
     *                 "created_at": "2017-03-17 10:27:30",
     *                 "id": 1,
     *                 "location_text": null,
     *                 "message": "I am OK",
     *                 "message_id": null,
     *                 "rollcall": {
     *                     "id": 1,
     *                     "uri": "/rollcalls/1"
     *                 },
     *                 "updated_at": null,
     *                 "uri": "/rollcalls/1/reply/1",
     *                 "user": {
     *                     "id": 1,
     *                     "role": "member",
     *                     "uri": "/users/1"
     *                 }
     *             }
     *         },
     *         "reply_count": 2,
     *         "send_via": null,
     *         "sent": 0,
     *         "sent_count": 4,
     *         "status": "pending",
     *         "updated_at": null,
     *         "uri": "/rollcalls/1",
     *         "user": {
     *             "id": 4,
     *             "uri": "/users/4"
     *         }
     *     }
     * })
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
     * @Post("/")
     * @Versions({"v1"})
     * @Request({
     *     "answers": {
     *         {
     *             "answer": "No",
     *         },
     *         {
     *             "answer": "Yes",
     *         }
     *     },
     *     "message": "Westgate under siege, are you ok?",
     *     "organization_id": 2,
     *     "recipients": {
     *         {
     *             "id": 3
     *         },
     *         {
     *             "id": 1
     *         }
     *     }
     *}, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "rollcall": {
     *         "answers": {
     *             "No",
     *             "Yes"
     *         },
     *         "complaint_count": 0,
     *         "created_at": "2017-03-18 19:19:27",
     *         "id": 6,
     *         "message": "Westgate under siege, are you ok?",
     *         "organization": {
     *             "id": 2,
     *             "uri": "/organizations/2"
     *         },
     *         "recipients": {
     *             {
     *                 "id": 3,
     *                 "uri": "/users/3"
     *             },
     *             {
     *                 "id": 1,
     *                 "uri": "/users/1"
     *             }
     *         },
     *         "replies": {},
     *         "send_via": null,
     *         "sent": 0,
     *         "status": "pending",
     *         "updated_at": "2017-03-18 19:19:27",
     *         "uri": "/rollcalls/6",
     *         "user": {
     *             "id": 5,
     *             "uri": "/users/5"
     *          }
     *     }
     * })
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

        if (!$this->creditService->hasSufficientCredits($roll_call)) {
            return response('Payment Required', 402);
        }

        // Send roll call
        dispatch((new SendRollCall($roll_call))/*->onQueue('rollcalls')*/);

        return $this->response->item($roll_call, new RollCallTransformer, 'rollcall');
    }

    /**
     * Update/resend a roll call
     *
     * @Put("/{roll_call_id}")
     * @Parameters({
     *   @Parameter("roll_call_id", type="number", required=true, description="Roll Call id")
     * })
     * @Versions({"v1"})
     * @Request({
     *     "organization_id": 2,
     *     "recipients": {
     *         {
     *             "id": 1
     *         },
     *         {
     *             "id": 2
     *         },
     *         {
     *             "id": 3
     *         }
     *     },
     *     "sent": 1,
     *     "status": "received"
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "rollcall": {
     *         "answers": null,
     *         "complaint_count": 0,
     *         "created_at": null,
     *         "id": 1,
     *         "message": "Westgate under siege",
     *         "organization": {
     *             "id": 2,
     *             "uri": "/organizations/2"
     *         },
     *         "recipients": {
     *             {
     *                 "id": 1,
     *                 "uri": "/users/1"
     *             },
     *             {
     *                 "id": 2,
     *                 "uri": "/users/2"
     *             },
     *             {
     *                 "id": 4,
     *                 "uri": "/users/4"
     *             }
     *         },
     *         "replies": {
     *             {
     *                 "answer": null,
     *                 "contact": {
     *                     "id": 1,
     *                     "uri": "/contacts/1"
     *                 },
     *                 "created_at": "2017-03-18 19:32:30",
     *                 "id": 1
     *             },
     *             {
     *                 "answer": null,
     *                 "contact": {
     *                     "id": 4,
     *                     "uri": "/contacts/4"
     *                 },
     *                 "created_at": "2017-03-17 19:32:30",
     *                 "id": 2
     *             },
     *             {
     *                 "answer": null,
     *                 "contact": {
     *                     "id": 4,
     *                     "uri": "/contacts/4"
     *                 },
     *                 "created_at": "2017-03-18 19:32:30",
     *                 "id": 3
     *             }
     *         },
     *         "send_via": null,
     *         "sent": 1,
     *         "status": "received",
     *         "updated_at": "2017-03-18 19:32:34",
     *         "uri": "/rollcalls/1",
     *         "user": {
     *             "id": 4,
     *             "uri": "/users/4"
     *         }
     *     }
     * })
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

            $roll_call_to_dispatch['recipients'] = array_filter(
                $request->input('recipients'),
                function ($recipient) use ($roll_call) {
                    return !$this->roll_calls->hasRepliedToRollCall($recipient['id'], $roll_call['id']);
                });

            if (!$this->creditService->hasSufficientCredits($roll_call_to_dispatch)) {
                return response('Payment Required', 402);
            }

            dispatch((new SendRollCall($roll_call_to_dispatch))/*->onQueue('rollcalls')*/);
        }

        return $this->response->item($roll_call, new RollCallTransformer, 'rollcall');
    }

    /**
     * Resend a roll call to a single recipient.
     *
     * @Post("/{roll_call_id}/recipients/{recipient_id}/messages")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("roll_call_id", type="number", required=true, description="Roll Call id"),
     *   @Parameter("recipient_id", type="number", required=true, description="Recipient id")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Responose(200, body={
     *  "recipient": {{
     *      "name":"Org owner",
     *      "description":"Org owner",
     *      "response_status":"waiting"
     *   }}
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function addMessage(SendRollCallRequest $request, $id, $recipient_id)
    {
        $this->roll_calls->updateRecipientStatus($id, $recipient_id, 'waiting');

        // Get roll call and send to recipient
        $roll_call = $this->roll_calls->find($id);

        $roll_call['recipients'] = [];

        array_push($roll_call['recipients'], [
            'id' => $recipient_id,
        ]);

        dispatch((new SendRollCall($roll_call))/*->onQueue('rollcalls')*/);

        $recipient = $this->roll_calls->getRecipient($id, $recipient_id);
        return $this->response->item($recipient, new UserTransformer, 'recipient');
    }

    /**
     * List roll call recipients
     *
     * @Get("/{roll_call_id}/recipients")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("roll_call_id", type="number", required=true, description="Roll Call id")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "recipients": {
     *         {
     *             "id": 1,
     *             "uri": "/users/1"
     *         },
     *         {
     *             "id": 2,
     *             "uri": "/users/2"
     *         },
     *         {
     *             "id": 4,
     *             "uri": "/users/4"
     *         }
     *     }
     * })
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
     * @Get("/{roll_call_id}/messages")
     * @Parameters({
     *   @Parameter("roll_call_id", type="number", required=true, description="Roll Call id")
     * })
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "messages": {
     *         {
     *             "contact": "+254721674180",
     *             "id": 1,
     *             "type": "phone",
     *             "uri": "/contacts/1",
     *             "user": {
     *                 "id": 1,
     *             }
     *         },
     *         {
     *             "contact": "linda@ushahidi.com",
     *             "id": 3,
     *             "type": "email",
     *             "uri": "/contacts/3",
     *             "user": {
     *                 "id": 2,
     *             }
     *         }
     *     }
     * })
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
     *
     * @Delete("/")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(405, body={
     *     "message": "405 Method Not Allowed",
     *     "status_code": 405
     * })
     */
    public function delete()
    {
        //
    }

}
