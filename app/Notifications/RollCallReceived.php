<?php

namespace RollCall\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Models\RollCall;
use RollCall\Models\Organization;
use RollCall\Http\Transformers\UserTransformer;
use Illuminate\Notifications\Messages\SlackMessage;

class RollCallReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(RollCall $roll_call)
    {
        $this->roll_call = $roll_call;
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
            'rollcall_message' => $this->roll_call->message,
            'rollcall_id' => $this->roll_call->id,
            'profile_picture' => $this->roll_call->user->profile_picture || null,
            'initials' => UserTransformer::generateInitials($this->roll_call->user->name),
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
        $org = Organization::findOrFail($this->roll_call->user->organization['id']);

        $client_url = $org->url();
        $domain = config('rollcall.domain');

        $params['roll_call_url'] = $client_url .'/rollcalls/'. $this->roll_call['id'];
        $params['message']= $this->roll_call['message'];
        $params['response_answers'] = '';

        foreach ($this->roll_call['answers'] as $index => $answer) {
            $answer_url = $client_url .'/rollcalls/'. $this->roll_call['id']. '/answer/' . $index;
            $params['response_answers'] .= "<" . $answer_url ."|" . $answer["answer"]. ">\t\t";
        }

        return (new SlackMessage)
                    ->success()
                    ->from($this->roll_call->user['name'], config('slack.from_emoji'))
                    // ->to($this->contact['contact'])
                    ->content('New RollCall from ' . $org['name'])
                    ->attachment(function ($attachment) use ($params) {
                        $attachment
                            ->title($params['message'], $params['roll_call_url'])
                            ->content($params['response_answers'])
                            ->markdown(['text']);
                    });

    }
}
