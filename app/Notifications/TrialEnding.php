<?php

namespace RollCall\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Models\Subscription;

use Carbon\Carbon;

class TrialEnding extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Your RollCall free trial is ending in ' . $this->days() . ' days. Hope you enjoyed using it!')
                    ->line('After this time we will automatically charge your account.')
                    ->action('Review my Payment Settings', $this->url())
                    ->line('Thank you for using RollCall!');
    }

    private function url()
    {
        return $this->subscription->organization->url('/settings/plan-and-credits');
    }

    private function days()
    {
        return (new Carbon($this->subscription->trial_ends_at))->diffInDays();
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
            'trial_ends_at' => $this->subscription->trial_ends_at,
            'days' => $this->days(),
        ];
    }
}
