<?php

namespace TenFour\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TenFour\Models\CheckIn;
use TenFour\Models\Organization;
use TenFour\Http\Transformers\UserTransformer;
use Illuminate\Notifications\Messages\SlackMessage;

class CheckInReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(CheckIn $check_in)
    {
        $this->check_in = $check_in;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'slack'];
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
            'check_in_message' => $this->check_in->message,
            'check_in_id' => $this->check_in->id,
            'profile_picture' => $this->check_in->user->profile_picture || null,
            'initials' => UserTransformer::generateInitials($this->check_in->user->name),
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
        $org = Organization::findOrFail($this->check_in->user->organization['id']);

        $client_url = $org->url();

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
                    ->content('New check-in from ' . $org['name'])
                    ->attachment(function ($attachment) use ($params) {
                        $attachment
                            ->title($params['message'], $params['check_in_url'])
                            ->content($params['response_answers'])
                            ->markdown(['text']);
                    });

    }
}
