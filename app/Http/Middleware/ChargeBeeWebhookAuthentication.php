<?php

namespace TenFour\Http\Middleware;

use Closure;

class ChargeBeeWebhookAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->getUser() === config('chargebee.webhook.username') &&
            $request->getPassword() ===  config('chargebee.webhook.password')) {
            return $next($request);
        }

        abort(403);
    }
}
