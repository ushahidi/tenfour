<?php

namespace TenFour\Http\Controllers;

use Log;
use App;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Contracts\Repositories\ReplyRepository;
use TenFour\Models\User;
use TenFour\Models\Organization;
use Illuminate\Http\Request;

use libphonenumber\NumberParseException;

class VoiceController extends Controller
{

    public function __construct(CheckInRepository $checkins, ReplyRepository $replies)
    {
        $this->checkins = $checkins;
        $this->replies = $replies;
    }

    public function handleReply(Request $request)
    {
        if (!$request->input('dtmf')) {
            return response('OK', 200);
        }

        $check_in_id = $request->input('check_in_id');
        $answer_id = ((int) $request->input('dtmf')) - 1;
        $recipient_id = (int) $request->input('recipient_id');
        $check_in = $this->checkins->find($check_in_id);

        $answer_text = $check_in['answers'][$answer_id]['answer'];

        $reply = $this->replies->addReply(
            [
              'user_id' => $recipient_id,
              'check_in_id' => $check_in_id,
              'answer' => $answer_text,
            ], $check_in_id);

        $this->checkins->updateRecipientStatus($check_in_id, $recipient_id, 'replied');

        return response('OK', 200);
    }

    public function handleEvent(Request $request)
    {
        $check_in_id = (int) $request->input('check_in_id');
        $recipient_id = (int) $request->input('recipient_id');
        $check_in = $this->checkins->find($check_in_id);

        if ($request->input('status')) {
            switch ($request->input('status')) {
            case 'ringing':
                // \Log::debug("UUID: {$request['conversation_uuid']} - ringing.");
                break;
            case 'answered':
                // \Log::debug("UUID: {$request['conversation_uuid']} - was answered.");

                // $reply = $this->replies->addReply(
                //     [
                //       'user_id' => $recipient_id,
                //       'check_in_id' => $check_in_id,
                //       'message' => 'Answered the voice call',
                //     ], $check_in_id);

                break;
            case 'machine':
                // \Log::debug("UUID: {$request['conversation_uuid']} - answering machine.");
                break;
            case 'complete':
                // If you set eventUrl in your NCCO. The recording download URL
                // is returned in recording_url. It has the following format
                // https://api.nexmo.com/media/download?id=52343cf0-342c-45b3-a23b-ca6ccfe234b0
                //
                // Make a GET request to this URL using JWT authentication to download
                // the recording. For more information, see
                // https://developer.nexmo.com/voice/voice-api/guides/record-calls-and-conversations
                // \Log::debug("UUID: {$request['conversation_uuid']} - complete.");
                break;
            default:
                break;
            }
        }

        return response('OK', 200);
    }

    public function makeCheckInNCCO(Request $request)
    {
        $check_in_id = $request->input('check_in_id');
        $check_in = $this->checkins->find($check_in_id);

        $recipient_id = $request->input('recipient_id');

        $sender_name = User::findOrFail($check_in['user_id'])->name;
        $org_name = Organization::findOrFail($check_in['organization_id'])->name;
        $answer_text = '';

        foreach ($check_in['answers'] as $key => $answer) {
            $answer_text .= 'Press ' . ($key+1) . ' for ' . $answer['answer'] . '. ';
        }

        $reply_url = "https://" . config('tenfour.domain') . "/voice/reply?check_in_id=" . $check_in_id . "&recipient_id=" . $recipient_id;

        return [
            [
                "action" => "talk",
                "text" => "This is a check-in from " . $sender_name . " at " . $org_name . ". " . $check_in['message'],
                "voiceName" => "Amy",
                "bargeIn" => false
            ],
            [
                "action" => "talk",
                "text" => $answer_text,
                "voiceName" => "Amy",
                "bargeIn" => true
            ],
            [
                "action" => "input",
                "submitOnHash" => true,
                "timeOut" => 20,
                "maxDigits" => 1,
                "eventUrl" => [$reply_url]
            ],
            [
                "action" => "talk",
                "text" => "Thank you for your response, goodbye.",
                "voiceName" => "Amy"
            ]
        ];
    }

}
