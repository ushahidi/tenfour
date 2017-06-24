<?php

namespace RollCall\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Models\Subscription;

class PaymentFailed extends Notification
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
                    ->line('We failed to make your payment for RollCall on the ' . $this->subscription->card_type . ' card ending in ' . $this->subscription->last_four . '.')
                    ->line('We will try to make payment on your card again.')
                    ->line('In the meantime, please check your card information in RollCall settings.')
                    ->action('Check my Payment Settings', $this->retryUrl())
                    ->line('Thank you for using RollCall!');
    }

    private function retryUrl()
    {
        return $this->subscription->organization->url('/settings/plan-and-credits');
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
            'card_type' => $this->subscription->card_type,
            'last_four' => $this->subscription->last_four,
            'url' => $this->retryUrl(),
        ];
    }
}
