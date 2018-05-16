<?php

namespace TenFour\Jobs;

use TenFour\Models\Subscription;
use TenFour\Contracts\Repositories\SubscriptionRepository;
use TenFour\Contracts\Services\PaymentService;
use TenFour\Notifications\FreePromoEnding;

use App;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NotifyFreePromoEnding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const NOTIFY_DAYS_BEFORE_FREE_PROMO_ENDING = 7;

    /**
     * Execute the job.
     *
     * @return mixed
     */
    public function handle(SubscriptionRepository $subscriptions)
    {
        $this->payments = App::make('TenFour\Contracts\Services\PaymentService');

        foreach ($subscriptions->all() as $sub) {
            if ($sub['promo_code'] && $sub['promo_ends_at']) {
                $promo_ends_at = Carbon::createFromTimestamp(strtotime($sub['promo_ends_at']));
                $diff_in_days = $promo_ends_at->diffInDays(Carbon::now());

                if (Carbon::now()->lt($promo_ends_at) && $diff_in_days == self::NOTIFY_DAYS_BEFORE_FREE_PROMO_ENDING) {
                    $coupon = $this->payments->retrieveCoupon($sub['promo_code']);

                    if ($coupon && $coupon['discount_percentage'] == 100) {
                        $subscription = Subscription::where('id', $sub['id'])->first();
                        $subscription->organization->owner()->notify(new FreePromoEnding($subscription));
                    }
                }
            }
        }
    }
}
