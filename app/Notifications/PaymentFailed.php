<?php

namespace TenFour\Notifications;

use TenFour\Services\URLFactory;
use TenFour\Models\Subscription;
use TenFour\Http\Transformers\UserTransformer;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

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
        $body = 'We failed to make your payment for TenFour on the ' . $this->subscription->card_type . ' card ending in ' . $this->subscription->last_four . '.<br><br>' .
            'We will try to make payment on your card again.<br><br>' .
            'In the meantime, please check your card information in TenFour settings.';

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->retryUrl(),
                'action_text'     => 'Review my Payment Settings',
                'subject'         => 'Payment Failed',
                'profile_picture' => $this->subscription->organization->profile_picture,
                'org_subdomain'   => $this->subscription->organization->subdomain,
                'org_name'        => $this->subscription->organization->name,
                'initials'        => UserTransformer::generateInitials($this->subscription->organization->name),
                'body'            => $body
            ])
            ->subject('Payment Failed');
    }

    private function retryUrl()
    {
        return URLFactory::makePaymentsURL($this->subscription->organization);
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
