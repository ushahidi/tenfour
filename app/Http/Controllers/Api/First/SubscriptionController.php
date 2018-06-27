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
     * Cancel a subscription.
     *
     * Effectively changes a subscription to a free plan.
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

        // $result = $this->payments->cancelSubscription($subscription->subscription_id);

        $result = $this->payments->changeToFreePlan($subscription->subscription_id);

        $subscription->update([
            'status'   => $result['subscription']['status'],
            'plan_id'  => $result['subscription']['plan_id']
        ]);

        $this->organizations->setSetting($organization_id, 'plan_and_credits', ['monthlyCreditsExtra' => 0]);

        $this->credits->clearCredits($organization_id);

        return $this->response->item($subscription->toArray(), new SubscriptionTransformer, 'subscription');
    }

    /**
     * Create a billing iframe URL for upgrading a subscription to pro.
     *
     * @Get("{org_id}/subscriptions/{subscription_id}/hostedpage/switchtopro?callback={callback}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("subscription_id", type="number", required=true, description="Subscription id"),
     *   @Parameter("callback", type="url", required=true, description="Callback URL"),
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
    public function getProUpgradeHostedPageUrl(CreateHostedPageRequest $request, $organization_id)
    {
        $organization = Organization::findOrFail($organization_id);

        if (count($organization->subscriptions) !== 1) {
            \Log::error('Refusing to get a hosted page url - Organization must have exactly one subscription.');
            return abort(403);
        }

        $url = $this->payments->getProUpgradeHostedPageUrl(
            $organization,
            $request->input('callback')
        );

        return response()->json(['url' => $url]);
    }

    /**
     * Create a billing iframe URL for updating payment information.
     *
     * @Get("{org_id}/subscriptions/{subscription_id}/hostedpage/update?callback={callback}")
     * @Versions({"v1"})
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("subscription_id", type="number", required=true, description="Subscription id"),
     *   @Parameter("callback", type="url", required=true, description="Callback URL"),
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
    public function getUpdatePaymentInfoHostedPageUrl(CreateHostedPageRequest $request, $organization_id)
    {
        $organization = Organization::findOrFail($organization_id);

        if (count($organization->subscriptions) !== 1) {
            \Log::error('Refusing to get a hosted page url - Organization must have exactly one subscription.');
            return abort(403);
        }

        $url = $this->payments->getUpdatePaymentInfoHostedPageUrl(
            $organization,
            $request->input('callback')
        );

        return response()->json(['url' => $url]);
    }
}
