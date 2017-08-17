<?php

namespace RollCall\Models;

use RollCall\Models\Organization;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Subscription
 */
class Subscription extends Model
{
    // use HandlesWebhooks;

    protected $fillable = ['subscription_id', 'customer_id', 'status', 'plan_id', 'organization_id', 'quantity', 'last_four', 'expiry_month', 'expiry_year', 'card_type', 'trial_ends_at', 'ends_at', 'next_billing_at', 'promo_code', 'promo_ends_at'];

    /**
     * @var array
     */
    protected $dates = ['ends_at', 'trial_ends_at', 'next_billing_at', 'promo_ends_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo('RollCall\Models\Organization');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addons()
    {
        return $this->hasMany('RollCall\Models\Addon');
    }

    /**
     * Check if a subscription is cancelled
     *
     * @return bool
     */
    public function cancelled()
    {
        return (!! $this->ends_at);
    }

    /**
     * Check if a subscription is active
     *
     * @return bool
     */
    public function active()
    {
        if (! $this->valid())
        {
            return $this->onTrial();
        }

        return true;
    }

    /**
     * Check if a subscription is within it's trial period
     *
     * @return bool
     */
    public function onTrial()
    {
        if (!! $this->trial_ends_at)
        {
            return Carbon::now()->lt($this->trial_ends_at);
        }

        return false;
    }

    /**
     * Check if the subscription is not expired
     *
     * @return bool
     */
    public function valid()
    {
        if (! $this->ends_at)
        {
            return true;
        }

        return Carbon::now()->lt($this->ends_at);
    }
}
