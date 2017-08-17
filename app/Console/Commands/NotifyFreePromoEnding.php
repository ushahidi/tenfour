<?php

namespace RollCall\Console\Commands;

use RollCall\Models\Subscription;
use RollCall\Contracts\Repositories\SubscriptionRepository;
use RollCall\Contracts\Services\PaymentService;
use RollCall\Notifications\FreePromoEnding;

use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyFreePromoEnding extends Command
{
    const NOTIFY_DAYS_BEFORE_FREE_PROMO_ENDING = 7;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:freepromoending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify the owner when their "Free Promo" is ending #696';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PaymentService $payments)
    {
        parent::__construct();

        $this->payments = $payments;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SubscriptionRepository $subscriptions)
    {
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
