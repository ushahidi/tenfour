<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Http\Requests\CheckIn\GetCheckInsRequest;
use TenFour\Http\Requests\CheckIn\GetCheckInRequest;
use TenFour\Http\Requests\CheckIn\CreateCheckInRequest;
use TenFour\Http\Requests\CheckIn\UpdateCheckInRequest;
use TenFour\Http\Requests\CheckIn\SendCheckInRequest;
use TenFour\Http\Requests\CheckIn\AddContactsRequest;
use TenFour\Http\Requests\Reply\AddReplyRequest;
use TenFour\Http\Requests\Reply\GetReplyRequest;
use TenFour\Http\Transformers\CheckInTransformer;
use TenFour\Http\Transformers\ContactTransformer;
use TenFour\Http\Transformers\ReplyTransformer;
use TenFour\Http\Transformers\UserTransformer;
use TenFour\Http\Response;
use TenFour\Jobs\SendCheckIn;
use TenFour\Services\CreditService;
use TenFour\Notifications\CheckInChanged;
use TenFour\Models\CheckIn;

use Illuminate\Support\Facades\Notification;
use Dingo\Api\Auth\Auth;
use App;
use TenFour\Models\ScheduledCheckIn;

/**
 * @Resource("Checkins", uri="/api/v1/organizations/{org_id}/checkins")
 */
class CheckInController extends ApiController
{
    public function __construct(CheckInRepository $check_ins, Auth $auth, Response $response, CreditService $creditService)
    {
        $this->check_ins = $check_ins;
        $this->auth = $auth;
        $this->response = $response;
        $this->creditService = $creditService;
    }

    /**
     * Get all check-ins for an organization
     *
     * @Get("/{?offset,limit,template}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("org_id", type="number", required=true, description="Organization id"),
     *     @Parameter("offset", default=0),
     *     @Parameter("limit", default=0),
     *     @Parameter("template", type="boolean", default=false, description="only retrieve check-in templates")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "checkins": {
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
     *                     "check_in": {
     *                         "id": 1,
     *                         "uri": "/organizations/2/checkins/1"
     *                     },
     *                     "updated_at": null,
     *                     "uri": "/organizations/2/checkins/1/reply/1",
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
     *             "uri": "/organizations/2/checkins/1",
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
     *                     "checkin": {
     *                         "id": 3,
     *                         "uri": "/organizations/2/checkins/3"
     *                     },
     *                     "updated_at": null,
     *                     "uri": "/organizations/2/checkins/3/reply/5",
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
     *             "uri": "/organizations/2/checkins/3",
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
     *             "uri": "/organizations/2/checkins/4",
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
    public function all(GetCheckInsRequest $request, $organization_id)
    {
        $user_id = null;

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);

        if ($request->query('user') === 'me') {
            $user_id = $this->auth->user()['id'];
        } else {
            $user_id = $request->query('user');
        }

        $check_ins = $this->check_ins->all(
            $request->route('organization'),
            $user_id,
            $request->input('recipient_id'),
            $this->auth->user()['id'],
            $offset,
            $limit,
            $request->input('template', false));

        return $this->response->collection($check_ins, new CheckInTransformer, 'checkins');
    }

    /**
     * Get a single check in
     *
     * @Get("/{check_in_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("check_in_id", type="number", required=true, description="check in id")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "checkin": {
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
     *                 "checkin": {
     *                     "id": 1,
     *                     "uri": "/organizations/2/checkins/1"
     *                 },
     *                 "updated_at": null,
     *                 "uri": "/organizations/2/checkins/1/reply/1",
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
     *         "uri": "/organizations/2/checkins/1",
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
    public function find(GetCheckInRequest $request, $organization_id, $check_in_id)
    {
        $check_in = $this->check_ins->find($check_in_id);
        return $this->response->item($check_in, new CheckInTransformer, 'checkin');
    }

    public function findById(GetCheckInRequest $request, $check_in_id)
    {
        return $this->find($request, null, $check_in_id);
    }

    /**
     * Create a check in
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
     *     "schedule": {
     *       "starts_at": "2019-03-4",
     *       "expires_at": "2019-03-20",
     *       "check_in_count": 20,
     *       "frequency": "weekly"
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
     *     },
     *     "group_ids": {},
     *     "user_ids": {},
     *     "everyone": false,
     *     "template": false
     *}, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "checkin": {
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
     *         "uri": "/organizations/2/checkins/6",
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
    public function create(CreateCheckInRequest $request, $organization_id)
    {
        $check_in = $this->check_ins->create($request->input() + [
            'user_id' => $this->auth->user()['id'],
        ]);
        if (!$this->creditService->hasSufficientCredits($check_in)) {
            return response('Payment Required', 402);
        }
        $schedule = $request->input('schedule');
        if ($schedule && $schedule['frequency'] && $schedule['frequency'] !== 'once') {
            // created scheduled check in that will be picked up by the laravel scheduler task
            $scheduled_check_in = new ScheduledCheckIn(
                [
                    'expires_at' => $schedule['expires_at'],
                    'starts_at' => $schedule['starts_at'],
                    'frequency' => $schedule['frequency'],
                    'remaining_count' => $schedule['remaining_count'],
                    'check_ins_id' => $check_in['id'],
                    'scheduled' => 0
                ]
            );
            $scheduled_check_in->save();
        } else {         
            // Send check-in
            dispatch((new SendCheckIn($check_in))/*->onQueue('checkins')*/);
        }
        return $this->response->item($check_in, new CheckInTransformer, 'checkin');
    }

    /**
     * Update/resend a check-in
     *
     * @Put("/{check_in_id}")
     * @Parameters({
     *   @Parameter("check_in_id", type="number", required=true, description="Check-in id")
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
     *     "checkin": {
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
     *         "uri": "/organizations/2/checkins/1",
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
    public function update(UpdateCheckInRequest $request, $organization_id, $check_in_id)
    {
        $check_in = $this->check_ins->update($request->all(), $check_in_id);

        // Send check-in to new recipients
        if ($request->input('recipients')) {
            $check_in_to_dispatch = $check_in;

            $check_in_to_dispatch['recipients'] = array_filter(
                $request->input('recipients'),
                function ($recipient) use ($check_in) {
                    return !$this->check_ins->hasRepliedToCheckIn($recipient['id'], $check_in['id']);
                });

            if (!$this->creditService->hasSufficientCredits($check_in_to_dispatch)) {
                return response('Payment Required', 402);
            }

            dispatch((new SendCheckIn($check_in_to_dispatch))/*->onQueue('checkins')*/);
        }

        if (!$request->input('template')) {
            Notification::send(CheckIn::findOrFail($check_in['id'])->recipients, new CheckInChanged($check_in));
            Notification::send(CheckIn::findOrFail($check_in['id'])->user, new CheckInChanged($check_in));
        }

        return $this->response->item($check_in, new CheckInTransformer, 'checkin');
    }

    /**
     * Resend a check-in request to a single recipient.
     *
     * @Post("/{check_in_id}/recipients/{recipient_id}/messages")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("check_in_id", type="number", required=true, description="Check-in id"),
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
     * @param int $check_in_id
     *
     * @return Response
     */
    public function addMessage(SendCheckInRequest $request, $organization_id, $check_in_id, $recipient_id)
    {
        $this->check_ins->updateRecipientStatus($check_in_id, $recipient_id, 'waiting');

        // Get check-in and send to recipient
        $check_in = $this->check_ins->find($check_in_id);

        $check_in['recipients'] = [];

        array_push($check_in['recipients'], [
            'id' => $recipient_id,
        ]);

        dispatch((new SendCheckIn($check_in))/*->onQueue('checkins')*/);

        $recipient = $this->check_ins->getRecipient($check_in_id, $recipient_id);
        return $this->response->item($recipient, new UserTransformer, 'recipient');
    }

    /**
     * List check-in recipients
     *
     * @Get("/{check_in_id}/recipients")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("check_in_id", type="number", required=true, description="Check-in id")
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
    public function listRecipients(GetCheckInRequest $request, $organization_id, $check_in_id)
    {
        return $this->response->collection($this->check_ins->getRecipients($check_in_id, $request->query('unresponsive')),
                                     new UserTransformer, 'recipients');
    }

    /**
     * List check-in messages
     *
     * @Get("/{check_in_id}/messages")
     * @Parameters({
     *   @Parameter("check_in_id", type="number", required=true, description="Check-in id")
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
    public function listMessages(GetCheckInRequest $request, $organization_id, $check_in_id)
    {
        return $this->response->collection($this->check_ins->getMessages($check_in_id),
                                     new ContactTransformer, 'messages');
    }

    /**
     * Delete a check-in
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
