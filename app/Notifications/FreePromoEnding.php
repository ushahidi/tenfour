<?php

namespace TenFour\Notifications;

use TenFour\Models\Subscription;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TenFour\Http\Transformers\UserTransformer;

/**
 * Notify beta users when their 100% discount promo code is about to expire.
 * This is distinct from a free trial ending scenario.
 * See: https://github.com/ushahidi/RollCall/issues/696
 */

class FreePromoEnding extends Notification
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
        $body = 'Your TenFour free promotion is ending in ' . $this->days() . ' days. Hope you enjoyed using it!<br><br>' .
            'After this time we will automatically charge your account.';

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->url(),
                'action_text'     => 'Review my Payment Settings',
                'subject'         => 'Free Promotion Ending',
                'profile_picture' => $this->subscription->organization->profile_picture,
                'org_subdomain'   => $this->subscription->organization->subdomain,
                'org_name'        => $this->subscription->organization->name,
                'initials'        => UserTransformer::generateInitials($this->subscription->organization->name),
                'body'            => $body
            ])
            ->subject('Free Promotion Ending');
    }

    private function url()
    {
        return $this->subscription->organization->url('/settings/plan-and-credits');
    }

    private function days()
    {
        return (new Carbon($this->subscription->promo_ends_at))->diffInDays();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $ending = (new Carbon($this->subscription->promo_ends_at))->toFormattedDateString();

        return [
            'global' => true,
            'message' => 'Your free promotion is scheduled to end on ' . $ending . ' and your credit card will be charged.',
            'expires' => (new Carbon($this->subscription->promo_ends_at))->toAtomString()
        ];
    }
}
