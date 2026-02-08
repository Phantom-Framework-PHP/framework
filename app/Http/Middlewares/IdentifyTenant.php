<?php

namespace Phantom\Http\Middlewares;

use Phantom\Core\Container;
use Phantom\Http\Request;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Phantom\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $tenantManager = Container::getInstance()->make('tenant');

        // 1. Check Header
        $tenantId = $request->header('X-Tenant-ID');

        // 2. Check Subdomain if header is not present
        if (!$tenantId) {
            $host = $request->header('Host');
            if ($host) {
                $parts = explode('.', $host);
                if (count($parts) > 2) {
                    $tenantId = $parts[0];
                }
            }
        }

        if ($tenantId) {
            $tenantManager->setTenant($tenantId);
        }

        return $next($request);
    }
}
