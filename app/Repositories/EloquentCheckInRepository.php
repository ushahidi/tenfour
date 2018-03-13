<?php
namespace TenFour\Repositories;

use TenFour\Models\CheckIn;
use TenFour\Models\Reply;
use TenFour\Contracts\Repositories\CheckInRepository;
use DB;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use TenFour\Notifications\CheckInReceived;

class EloquentCheckInRepository implements CheckInRepository
{
    public function __construct()
    {
    }

    public function all($org_id = null, $user_id = null, $recipient_id = null, $auth_user_id = null, $offset = 0, $limit = 0)
    {

        $query = CheckIn::query()
          ->orderBy('created_at', 'desc')
          ->with(['replies' => function ($query) {
            // Just get the most recent replies for each user
            $query->where('replies.created_at', DB::raw("(SELECT max(`r2`.`created_at`) FROM `replies` AS r2 WHERE `r2`.`user_id` = `replies`.`user_id` AND `r2`.`check_in_id` = `replies`.`check_in_id`)"));
          }]);

        if ($limit > 0) {
          $query
            ->offset($offset)
            ->limit($limit);
        }

        if ($org_id) {
            $query->where('organization_id', $org_id);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($recipient_id) {
            $query->whereHas('recipients', function ($query) use ($recipient_id) {
                $query->where('user_id', $recipient_id);
            });

            $query->orWhere('user_id', $recipient_id);
        }

        $check_ins = $query->get()->toArray();


        foreach($check_ins as $key => &$check_in)
        {
            // exclude others' self tests, but include my own
            if ($auth_user_id && $check_in['self_test_check_in'] && $check_in['user_id'] !== $auth_user_id) {
                unset($check_ins[$key]);
                continue;
            }

            $check_in += $this->getCounts($check_in['id']);
        }

        return $check_ins;
    }

    public function find($id)
    {
        $check_in = CheckIn::query()
            ->with(['replies' => function ($query) {
                // Just get the most recent replies for each user
                $query->where('replies.created_at', DB::raw("(SELECT max(`r2`.`created_at`) FROM `replies` AS r2 WHERE `r2`.`user_id` = `replies`.`user_id` AND `r2`.`check_in_id` = `replies`.`check_in_id`)"));
                $query->with('user');
            }])
            ->findOrFail($id)
            ->toArray();

        return $check_in + $this->getCounts($check_in['id']);
    }

    public function create(array $input)
    {
        $check_in = CheckIn::create($input);

        $userIds = collect($input['recipients'])->pluck('id')->all();
        $check_in->recipients()->sync($userIds);

        if (!$check_in['self_test_check_in']) {
            $this->notifyCheckIn($check_in);
        }

        return $check_in->fresh()
            ->toArray();
    }

    protected function notifyCheckIn($check_in) {

        $organizations = resolve('TenFour\Contracts\Repositories\OrganizationRepository');
        $channels = $organizations->getSetting($check_in['organization_id'], 'channels');

        if (isset($channels->slack) &&
            isset($channels->slack->enabled) &&
            isset($channels->slack->webhook_url) &&
            in_array('slack', $check_in['send_via'])) {
            $check_in->_slack_webhook_url = $channels->slack->webhook_url;
        }

        Notification::send($check_in->recipients, new CheckInReceived($check_in));
        Notification::send($check_in, new CheckInReceived($check_in));
    }

    public function update(array $input, $id)
    {
        $input = array_only($input, ['status', 'sent', 'recipients']);

        $check_in = CheckIn::findorFail($id);

        if (isset($input['sent'])) {
            $check_in->sent = $input['sent'];
        }

        if (isset($input['status'])) {
            $check_in->status = $input['status'];
        }

        $check_in->save();

        if (isset($input['recipients'])) {
            $userIds = collect($input['recipients'])->pluck('id')->all();
            $check_in->recipients()->syncWithoutDetaching($userIds);
        }

        return $check_in->fresh()->toArray();
    }

    public function getMessages($id, $user_id = null, $contact_id = null)
    {
        $query = CheckIn::findOrFail($id)->messages()
            ->with('user');

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        return $query->get()->toArray();
    }

    public function updateRecipientStatus($check_in_id, $user_id, $status)
    {
        DB::table('check_in_recipients')
            ->where('check_in_id', '=', $check_in_id)
            ->where('user_id', '=', $user_id)
            ->update(['response_status' => $status]);
    }

    public function setReplyToken($check_in_id, $user_id) {
        $reply_token = DB::table('check_in_recipients')
            ->where('check_in_id', '=', $check_in_id)
            ->where('user_id', '=', $user_id)
            ->value('reply_token');

        if ($reply_token) {
            // always reuse, never overwrite a reply token
            return $reply_token;
        }

        $token = Hash::Make(config('app.key'));

        DB::table('check_in_recipients')
            ->where('check_in_id', '=', $check_in_id)
            ->where('user_id', '=', $user_id)
            ->update(['reply_token' => $token]);

        return $token;
    }

    public function getUserFromReplyToken($reply_token) {
        return DB::table('check_in_recipients')
                    ->where('reply_token', '=', $reply_token)
                    ->value('user_id');
    }

    public function getReplyToken($check_in_id, $user_id) {
        return DB::table('check_in_recipients')
                    ->where('check_in_id', '=', $check_in_id)
                    ->where('user_id', '=', $user_id)
                    ->value('reply_token');
    }

    public function getLastSentMessageId($contact_id = null, $from = null)
    {
        $query = DB::table('check_in_messages')
               ->select('check_in_id');

        if ($contact_id) {
            $query->where('contact_id', $contact_id);
        }

        if ($from) {
            $query->where('from', $from);
        }

        return $query->orderBy('check_in_id', 'desc')->take(1)->value('check_in_id');
    }

    public function getSentCheckInId($contact_id, $check_in_id)
    {
        $query = DB::table('check_in_messages')
               ->select('check_in_id');

        $query->where('contact_id', $contact_id);
        $query->where('check_in_id', $check_in_id);

        return $query->orderBy('check_in_id', 'desc')->take(1)->value('check_in_id');
    }

    public function getRecipient($id, $recipient_id)
    {
        return CheckIn::findOrFail($id)->recipients()
            ->where('user_id', '=', $recipient_id)
            ->get()
            ->first()
            ->toArray();
    }

    public function getRecipients($id, $unresponsive=null)
    {
        return CheckIn::findOrFail($id)->recipients()
               ->get()
               ->toArray();
    }

    public function getLastUnrepliedByContact($contact_id, $from)
    {
        $unreplied = DB::table('check_in_messages')
            ->leftJoin('replies', function ($join) {
                $join->on('check_in_messages.check_in_id', '=', 'replies.check_in_id');
            })
            ->leftJoin('check_ins', function ($join) {
                $join->on('check_in_messages.check_in_id', '=', 'check_ins.id');
            })
            ->where('check_in_messages.contact_id', '=', $contact_id)
            ->where('check_in_messages.from', '=', $from)
            ->where('check_ins.answers', '!=', '[]')
            ->whereNull('replies.id')
            ->orderBy('check_in_messages.check_in_id', 'desc')
            ->take(1)
            ->select('check_in_messages.check_in_id', 'check_in_messages.from')
            ->get()
            ->toArray();

        if ($unreplied && count($unreplied)) {
            return [
              'id' => $unreplied[0]->check_in_id,
              'from' => $unreplied[0]->from
            ];
        } else {
            return null;
        }
    }

    public function getOutgoingNumberForCheckInToContact($check_in_id, $contact_id)
    {
      return DB::table('check_in_messages')
          ->where('check_in_id', '=', $check_in_id)
          ->where('contact_id', '=', $contact_id)
          ->value('from');
    }

    public function isOutgoingNumberActive($contact_id, $from)
    {
        $messages_with_no_reply = DB::table('check_in_messages')
            ->leftJoin('replies', function ($join) {
                $join->on('check_in_messages.check_in_id', '=', 'replies.check_in_id');
            })
            ->where('check_in_messages.contact_id', '=', $contact_id)
            ->where('check_in_messages.from', '=', $from)
            ->whereNull('replies.id')
            ->get()
            ->toArray();

        return count($messages_with_no_reply) > 0;
    }

    public function hasRepliedToCheckIn($user_id, $check_in_id)
    {
          $replies = DB::table('check_in_messages')
            ->leftJoin('replies', function ($join) {
                $join->on('check_in_messages.check_in_id', '=', 'replies.check_in_id');
            })
            ->where('replies.user_id', '=', $user_id)
            ->where('check_in_messages.check_in_id', '=', $check_in_id)
            ->get()
            ->toArray();

          return count($replies) > 0;
    }

    public function getLastUnrepliedByUser($user_id)
    {
        return DB::table('check_in_messages')
            ->leftJoin('contacts', function ($join) {
                $join->on('check_in_messages.contact_id', '=', 'contacts.id');
            })
            ->leftJoin('replies', function ($join) {
                $join->on('replies.check_in_id', '=', 'check_in_messages.check_in_id');
            })
            ->where('contacts.user_id', '=', $user_id)
            ->whereNull('replies.id')
            ->orderBy('check_in_messages.check_in_id', 'desc')
            ->take(1)
            ->value('check_in_messages.check_in_id');
    }

    public function addMessage($id, $contact_id, $from)
    {
        $check_in = CheckIn::findorFail($id);

        $check_in->messages()->attach($contact_id, ['from' => $from]);

        return CheckIn::findorFail($id)
            ->messages()
            ->where('contact_id', $contact_id)
            ->get()
            ->toArray();
    }

    public function delete($id)
    {
        //
    }

    protected function getReplyCounts($id)
    {
        return Reply::where('check_in_id', $id)
            ->count(DB::raw('DISTINCT `user_id`')); // only count each user once
    }

    protected function getSentCounts($id)
    {
        return DB::table('check_in_messages')
            ->where('check_in_id', $id)
            ->count();
    }

    public function getCounts($check_in_id)
    {
        return [
            'reply_count' => $this->getReplyCounts($check_in_id),
            'sent_count' => $this->getSentCounts($check_in_id)
        ];
    }

    public function setComplaintCount($count, $id)
    {
        $check_in = CheckIn::findOrFail($id);
        $check_in->complaint_count = $count;
        $check_in->save();

        return $check_in->fresh()->toArray();
    }

    public function getComplaintCountByOrg($org_id)
    {
        return CheckIn::where('organization_id', $org_id)
            ->sum('complaint_count');
    }
}
