<?php

namespace TenFour\Notifications;

use TenFour\Models\CheckIn as CheckInModel;
use TenFour\Models\User;
use TenFour\Models\Organization;
use TenFour\Models\Contact;
use TenFour\Channels\SMS as SMSChannel;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Services\URLFactory;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;

class CheckInFollowUp extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $check_in_id, Organization $organization, string $from)
    {
        $this->check_in = CheckInModel::findOrFail($check_in_id);
        $this->organization = $organization;
        $this->from = $from;
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
        return [SMSChannel::class];
    }

    public function toSMS($notifiable)
    {
        $reminder_reply_token = $this->check_in_repo->getReplyToken($this->check_in->id, $notifiable->user->id);

        $check_in_url = URLFactory::makeCheckInURL($this->organization, $this->check_in->id, $notifiable->user->id, $reminder_reply_token);
        $check_in_url = URLFactory::shorten($check_in_url);

        return [
            'from'        => $this->from,
            'to'          => $notifiable->contact,
            'msg'         => $check_in_url,
            'view'        => 'sms.unresponsive',
            'sms_type'    => 'reminder',
            'check_in_id' => $this->check_in->id

        ];
    }

}
