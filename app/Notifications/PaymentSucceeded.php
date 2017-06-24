<?php

namespace RollCall\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Models\Subscription;
use RollCall\Models\CreditAdjustment;

class PaymentSucceeded extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription, CreditAdjustment $creditAdjustment)
    {
        $this->subscription = $subscription;
        $this->creditAdjustment = $creditAdjustment;
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
                    ->line('You just successfully authorized a payment for RollCall on the ' . $this->subscription->card_type . ' card ending in ' . $this->subscription->last_four . '.')
                    ->line('We have applied ' . $this->creditAdjustment->adjustment . ' credits to your account.')
                    ->line('Your account now has ' . $this->creditAdjustment->balance . ' credits.')
                    ->action('Review my Payment Settings', $this->url())
                    ->line('Thank you for using RollCall!');
    }

    private function url()
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
            'adjustment' => $this->creditAdjustment->adjustment,
            'url' => $this->url(),
        ];
    }
}
