<?php

namespace TenFour\Notifications;

use TenFour\Models\User;
use TenFour\Models\CheckIn as CheckInModel;
use TenFour\Models\Organization;
use TenFour\Http\Transformers\UserTransformer;
use TenFour\Channels\FCM as FCMChannel;
use TenFour\Channels\CheckInMail as CheckInMailChannel;
use TenFour\Channels\CheckInSMS as CheckInSMSChannel;
use TenFour\Channels\Voice as VoiceChannel;
use TenFour\Mail\CheckIn as CheckInMail;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Services\URLFactory;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

use App;

class CheckIn extends Notification
{
    use Queueable;

    // private $credits, $recipient_count, $contact_count;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $check_in, Organization $organization)
    {
        $this->check_in = $check_in;
        $this->organization = $organization;
        $this->sender = User::findOrFail($this->check_in['user_id']);
        $this->check_in_repo = App::make('TenFour\Contracts\Repositories\CheckInRepository');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if (!isset($this->check_in['send_via'])) {
            $this->check_in['send_via'] = [];
        }

        $channels = [];

        $map = [
            "database"  => "database",
            "email"     => CheckInMailChannel::class,
            "sms"       => CheckInSMSChannel::class,
            "slack"     => "slack",
            "app"       => FCMChannel::class,
            "voice"     => VoiceChannel::class,
            // "whatsapp"  => WhatsAppChannel::class,
        ];

        if (class_basename($notifiable) === 'CheckIn') {
            if (in_array('slack', $this->check_in['send_via'])) {
                array_push($channels, 'slack');
            }
        }
        else if (class_basename($notifiable) === 'User') {
            if (in_array('app', $this->check_in['send_via'])) {
                array_push($channels, 'app');
            }
            array_push($channels, 'database');
        }
        else if (class_basename($notifiable) === 'Contact') {
            if ($notifiable->type === 'phone' && in_array('sms', $this->check_in['send_via']) ) {
                array_push($channels, 'sms');
            }
            if ($notifiable->type === 'email' && in_array('email', $this->check_in['send_via'])) {
                array_push($channels, 'email');
            }
            if ($notifiable->type === 'phone' && in_array('voice', $this->check_in['send_via'])) {
                array_push($channels, 'voice');
            }
        }

        return array_map(function ($channel) use ($map) {
            return $map[$channel];
        }, $channels);
    }

    public function toMail($contact)
    {
        $reply_token = $this->check_in_repo->getReplyToken($this->check_in['id'], $contact->user_id);

        $mail = new CheckInMail(
            $this->check_in,
            $this->organization->toArray(),
            $this->sender->toArray(),
            $contact->toArray(),
            User::findOrFail($contact->user_id)->toArray(),
            $reply_token);

        $this->check_in_repo->addMessage($this->check_in['id'], $contact['id'], $mail->getFromAddress(), $contact['contact'], 'mail');

        return $mail->build();
    }

    public function toSMS($contact)
    {
        $params = [];
        $reply_token = $this->check_in_repo->getReplyToken($this->check_in['id'], $contact->user_id);

        $check_in_url = URLFactory::makeCheckInURL(
            $this->organization,
            $this->check_in['id'],
            $contact->user_id,
            $reply_token
          );

        $params['check_in_url'] = URLFactory::shorten($check_in_url);
        $params['check_in_id'] = $this->check_in['id'];
        $params['answers'] = $this->check_in['answers'];
        $params['sender_name'] = $this->sender->name;
        $params['org_name'] = $this->organization->name;
        $params['msg'] = $this->check_in['message'];
        $params['from'] = $this->getFromNumber($contact);

        if ($params['from'] !== 'REPLIABLE') {
            $params['_last_unreplied_check_in_id'] = $this->getLastUnrepliedCheckIn($contact, $params['from']);
        }


        $this->check_in_repo->addMessage($this->check_in['id'], $contact['id'], $params['from'], $contact['contact'], 'sms', 1);

        return $params;
    }

    private function getLastUnrepliedCheckIn($contact, $from)
    {
        if (!$this->check_in_repo->isOutgoingNumberActive($contact['id'], $from)) {
            return;
        }

        $unreplied_sms_check_in = $this->check_in_repo->getLastUnrepliedByContact($contact['id'], $from);

        if ($unreplied_sms_check_in['id'] === $this->check_in['id']) {
            return;
        }

        if (!$unreplied_sms_check_in['from']) {
            return;
        }

        return $unreplied_sms_check_in['id'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'check_in_message' => $this->check_in['message'],
            'check_in_id' => $this->check_in['id'],
            'profile_picture' => $this->sender->profile_picture || null,
            'initials' => UserTransformer::generateInitials($this->sender->name),
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $client_url = $this->organization->url();

        $params['check_in_url'] = $client_url .'/checkins/'. $this->check_in['id'];
        $params['message']= $this->check_in['message'];
        $params['response_answers'] = '';

        foreach ($this->check_in['answers'] as $index => $answer) {
            $answer_url = $client_url .'/checkins/'. $this->check_in['id']. '/answer/' . $index;
            $params['response_answers'] .= "<" . $answer_url ."|" . $answer["answer"]. ">\t\t";
        }

        return (new SlackMessage)
                    ->success()
                    ->from($this->check_in->user['name'], config('slack.from_emoji'))
                    // ->to($this->contact['contact'])
                    ->content('New check-in from ' . $this->organization->name)
                    ->attachment(function ($attachment) use ($params) {
                        $attachment
                            ->title($params['message'], $params['check_in_url'])
                            ->content($params['response_answers'])
                            ->markdown(['text']);
                    });

    }

    public function toFCM($notifiable)
    {
        return [
            'type'        => 'checkin:created',
            'subject'     => 'TenFour Check-In',
            'msg'         => $this->check_in['message'],
            'org_name'    => $this->organization->name,
            'org_id'      => $this->organization->id,
            'checkin_id'  => $this->check_in['id'],
            'sender_name' => $this->sender->name,
            'sender_id'   => $this->sender->id
        ];
    }

    private function getFromNumber($contact) {
        // if there is already a from number used for this contact/check-in then use that
        $previously_sent_from = $this->check_in_repo->getOutgoingNumberForCheckInToContact($this->check_in['id'], $contact['id']);

        if ($previously_sent_from) {
            return $previously_sent_from;
        }

        $to = App::make('TenFour\Messaging\PhoneNumberAdapter');
        $to->setRawNumber($contact->contact);

        $region_code = $to->getRegionCode();

        $from = config('tenfour.messaging.sms_providers.'.$region_code.'.from');

        if (! $from) {
            $from = config('tenfour.messaging.sms_providers.default.from');
        }

        if (is_array($from)) {
            if (!config('tenfour.messaging.skip_number_shuffle')) {
                shuffle($from);
            }

            // if possible, get an outgoing number with no unreplied check-ins for this user
            foreach ($from as $from_number) {
                if (!$this->check_in_repo->isOutgoingNumberActive($contact['id'], $from_number)) {
                    return $from_number;
                }
            }

            return $from[0];
        } else {
            return $from;
        }
    }

    public function toVoice($contact)
    {
        $recipient_id = $contact->user_id;
        $check_in_id = $this->check_in['id'];
        $contact_id = $contact->id;
        $from = config('sms.nexmo.outgoing_call_number');

        $this->check_in_repo->addMessage($this->check_in['id'], $contact['id'], $from, $contact['contact'], 'voice', 1);

        return [
           'to' => [[
               'type' => 'phone',
               'number' => $contact->contact
           ]],
           'from' => [
               'type' => 'phone',
               'number' => $from
           ],
           'answer_url' => [URLFactory::makeVoiceAnswerURL($check_in_id, $recipient_id, $contact_id)],
           'event_url' => [URLFactory::makeVoiceEventURL($check_in_id, $recipient_id, $contact_id)],
           // 'machine_detection' => 'continue'
         ];
    }
}
