<?php

namespace TenFour\Notifications;

use TenFour\Models\Subscription;
use TenFour\Http\Transformers\UserTransformer;
use TenFour\Contracts\Services\PaymentService;
use TenFour\Channels\FCM as FCMChannel;
use TenFour\Serivces\URLFactory;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Carbon\Carbon;

class SubscriptionChanged extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription, PaymentService $payments)
    {
        $this->subscription = $subscription;
        $this->payments = $payments;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', FCMChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $body = 'Your TenFour subscription has changed. <ul>';

        if ($this->subscription->plan_id === $this->payments->getProPlanId()) {
            $body .= '<li>You are on the Pro Plan.</li>';
        }
        else if ($this->subscription->plan_id === $this->payments->getFreePlanId())
        {
            $body .= '<li>You are on the Free Plan.</li>';
        }

        foreach ($this->subscription->addons as $addon) {
            if ($addon->addon_id === $this->payments->getCreditBundleAddonId()) {
                $body .= '<li>You have ' . $addon->quantity . ' extra monthly credits.</li>';
            }
            else if ($addon->addon_id === $this->payments->getUserBundleAddonId()) {
                $body .= '<li>You have ' . $addon->quantity . ' extra monthly user bundles (25 users each).</li>';
            }
        }

        if ($this->subscription->plan_id === $this->payments->getProPlanId()) {
            $body .= '<li>Your next billing date is ' . (new Carbon($this->subscription->next_billing_at))->toFormattedDateString() . '</li>';
            $body .= '<li>Your next bill is estimated to be USD $' . number_format($this->payments->estimateBill($this->subscription), 2)  . '</li>';

            if ($this->subscription->promo_code) {
                $body .= '<li>Your discount code <strong>' . $this->subscription->promo_code . '</strong> will be applied to this bill.</li>';
            }
        }

        $body .= '</ul>';

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->url(),
                'action_text'     => 'Review my Payment Settings',
                'subject'         => 'Subscription Changed',
                'profile_picture' => $this->subscription->organization->profile_picture,
                'org_subdomain'   => $this->subscription->organization->subdomain,
                'org_name'        => $this->subscription->organization->name,
                'initials'        => UserTransformer::generateInitials($this->subscription->organization->name),
                'body'            => $body
            ])
            ->subject('Subscription Changed');
    }

    private function url()
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
        $ret = [
            'plan_id' => $this->subscription->plan_id,
            'status' => $this->subscription->status,
            'next_billing_at' => $this->subscription->next_billing_at,
            'addons' => [],
            'estimate' => $this->payments->estimateBill($this->subscription)
        ];

        foreach ($this->subscription->addons as $addon) {
            array_push($ret['addons'], [
                'addon_id' => $addon->addon_id,
                'quantity' => $addon->quantity
            ]);
        }

        return $ret;
    }

    /**
     * Get the fcm representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toFCM($notifiable)
    {
        return [
            'type'            => 'subscription:changed',
            'subject'         => 'Subscription Changed',
            'msg'             => null,
            'plan_id'         => $this->subscription->plan_id,
            'status'          => $this->subscription->status
        ];
    }
}
