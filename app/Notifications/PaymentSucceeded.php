<?php

namespace TenFour\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TenFour\Models\Subscription;
use TenFour\Models\CreditAdjustment;
use TenFour\Http\Transformers\UserTransformer;

class PaymentSucceeded extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription, $creditAdjustment)
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
        $body = 'You just successfully authorized a payment for TenFour on the ' . $this->subscription->card_type . ' card ending in ' . $this->subscription->last_four . '. <br><br>' .
            'We have applied ' . $this->creditAdjustment->adjustment . ' credits to your account. <br><br>' .
            'Your account now has ' . $this->creditAdjustment->balance . ' credits.';

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->url(),
                'action_text'     => 'Review my Payment Settings',
                'subject'         => 'Payment Succeeded',
                'profile_picture' => $this->subscription->organization->profile_picture,
                'org_subdomain'   => $this->subscription->organization->subdomain,
                'org_name'        => $this->subscription->organization->name,
                'initials'        => UserTransformer::generateInitials($this->subscription->organization->name),
                'body'            => $body
            ])
            ->subject('Payment Succeeded');
    }

    private function url()
    {
        return $this->subscription->organization->url('/#/settings/payments');
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
