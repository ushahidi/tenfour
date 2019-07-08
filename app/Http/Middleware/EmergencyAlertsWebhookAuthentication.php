<?php

namespace TenFour\Http\Middleware;

use Closure;

class EmergencyAlertsWebhookAuthentication
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
        if ($request->getUser() === config('emergency-alerts.webhook.username') &&
            $request->getPassword() ===  config('emergency-alerts.webhook.password')) {
            return $next($request);
        }
        abort(403);
    }
}
