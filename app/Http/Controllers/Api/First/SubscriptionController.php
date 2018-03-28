<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Http\Requests\Subscription\CreateSubscriptionRequest;
use TenFour\Http\Requests\Subscription\UpdateSubscriptionRequest;
use TenFour\Http\Requests\Subscription\GetSubscriptionRequest;
use TenFour\Http\Requests\Subscription\DeleteSubscriptionRequest;
use TenFour\Http\Requests\Subscription\CreateHostedPageRequest;
use TenFour\Models\Organization;
use TenFour\Models\Subscription;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Contracts\Repositories\SubscriptionRepository;
use TenFour\Contracts\Services\PaymentService;
use TenFour\Services\CreditService;
use Dingo\Api\Auth\Auth;
use TenFour\Http\Transformers\OrganizationTransformer;
use TenFour\Http\Transformers\SubscriptionTransformer;
use TenFour\Http\Response;
use Illuminate\Http\Request;
use DB;

/**
 * @Resource("Subscriptions", uri="/api/v1/organizations")
 */
class SubscriptionController extends ApiController
{

    public function __construct(SubscriptionRepository $subscriptions, Auth $auth, Response $response, OrganizationRepository $organizations, PaymentService $payments, CreditService $credits)
    {
        $this->subscriptions = $subscriptions;
        $this->auth = $auth;
        $this->response = $response;
        $this->organizations = $organizations;
        $this->payments = $payments;
        $this->credits = $credits;
    }

    /**
     * Get all subscriptions
     *
     * @Get("{org_id}/subscriptions")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "subscriptions": {
     *         "id": "1",
     *         "status": "in_trial",
     *         "card_type": "Visa",
     *         "last_four": "1111",
     *         "expiry_month": 12,
     *         "expiry_year": 1,
     *         "trial_ends_at": "2017-06-02 15:38:33",
     *         "next_billing_at": "2017-06-02 15:38:33",
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function index(GetSubscriptionRequest $request, $organization_id) {
        $subscriptions = $this->subscriptions->all($organization_id);

        return $this->response->collection($subscriptions, new SubscriptionTransformer, 'subscriptions');
    }

    /**
     * Get a single subscription
     *
     * @Get("{org_id}/subscriptions/{subscription_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("subscription_id", type="number", required=true, description="Subscription id")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "subscription": {
     *         "id": "1",
     *         "status": "in_trial",
     *         "card_type": "Visa",
     *         "last_four": "1111",
     *         "expiry_month": 12,
     *         "expiry_year": 1,
     *         "trial_ends_at": "2017-06-02 15:38:33",
     *         "next_billing_at": "2017-06-02 15:38:33"
     *     }
     * })
     *
     * @param Request $request
     * @return Response
     */
    public function show(GetSubscriptionRequest $request, $organization_id, $subscription_id)
    {
        $subscription = $this->subscriptions->find($organization_id, $subscription_id);
        return $this->response->item($subscription, new SubscriptionTransformer, 'subscription');
    }

    /**
     * Cancel a subscription
     *
     * @Delete("{org_id}/subscriptions/{subscription_id}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("subscription_id", type="number", required=true, description="Subscription id")
     * })
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *   "subscription": {
     *        "id": 1,
     *        "status": "cancelled",
     *    }
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function destroy(DeleteSubscriptionRequest $request, $organization_id, $subscription_id)
    {
        $subscription = Subscription::findOrFail($subscription_id);

        $result = $this->payments->cancelSubscription($subscription->subscription_id);

        $subscription->update([
            'status'   => $result['subscription']['status'],
        ]);

        return $this->response->item($subscription->toArray(), new SubscriptionTransformer, 'subscription');
    }

    /**
     * Create an ChargeBee Hosted Page subscription url
     *
     * @Post("{org_id}/subscriptions/hostedpage")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id")
     * })
     * @Request({
     *     "callback": "http://subdomain.tenfour.org/callback",
     * }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
     *     "url": "http://api.chargebee.com/hostedpage?xxx"
     * })
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createHostedPage(CreateHostedPageRequest $request, $organization_id)
    {
        $organization = Organization::findOrFail($organization_id);

        if (count($organization->subscriptions) >= 1) {
            return abort(403);
        }

        $planAndCreditsSettings = $this->organizations->getSetting($organization_id, 'plan_and_credits');

        $url = $this->payments->checkoutHostedPage(
            $organization,
            $request->input('callback'),
            $planAndCreditsSettings->monthlyCreditsExtra,
            $request->input('is_free_trial')
        );

        return response()->json(['url' => $url]);
    }

    /**
     * Create a ChargeBee hosted page url for updating the subscription
     *
     * @Put("{org_id}/subscriptions/hostedpage")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("subscription_id", type="number", required=true, description="Subscription id")
     * })
     * @Request({
     *     "callback": "http://subdomain.tenfour.org/callback",
     * })
     * @Response(200, body={
     *     "url": "http://api.chargebee.com/hostedpage?xxx"
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function updateHostedPage(CreateHostedPageRequest $request, $organization_id)
    {
        $organization = Organization::findOrFail($organization_id);

        if (count($organization->subscriptions) !== 1) {
            return abort(403);
        }

        $url = $this->payments->checkoutUpdateHostedPage(
            $organization,
            $request->input('callback')
        );

        return response()->json(['url' => $url]);
    }

    /**
     * API endpoint called by client after successful ChargeBee subscription creation
     *
     * @Post("{org_id}/subscriptions/hostedpage/confirm")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("subscription_id", type="number", required=true, description="Subscription id")
     * })
     * @Request({
     *     "subscription_id": "cb123uijh12iu3h87",
     * })
     * @Response(200, body={
     *   "subscription": {
     *        "id": 1,
     *        "status": "active",
     *    }
     * })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function confirmHostedPage(GetSubscriptionRequest $request, $organization_id)
    {
        $hostedPage = $this->payments->retrieveHostedPage($request->subscription_id);

        if ((int) $hostedPage->organization_id !== (int) $organization_id) {
            return abort(403);
        }

        $subscription_id = $hostedPage->content['customer']['id'];

        $result = $this->payments->retrieveSubscription($subscription_id);

        $subscription = $this->subscriptions->create($organization_id, $result);

        if ($subscription['promo_code']) {
            // https://github.com/ushahidi/RollCall/issues/735
            $coupon = $this->payments->retrieveCoupon($subscription['promo_code']);

            if ($coupon && $coupon['discount_percentage'] == 100) {
                $this->credits->addCreditAdjustment($organization_id, config('credits.freepromo'), 'topup', ['rc_freepromo' => true]);
            }
        }

        if ($subscription['status'] === 'cancelled') {
            $this->payments->reactivateSubscription($subscription_id);
        }

        return $this->response->item($subscription, new SubscriptionTransformer, 'subscription');
    }

}
