<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a unique request ID to every incoming request, propagates it to:
 * - response headers (X-Request-ID)
 * - structured log context (so log lines can be correlated)
 * - view share (so frontend can include it in support requests)
 *
 * Honors an inbound X-Request-ID from upstream proxies (nginx, Cloudflare)
 * to enable end-to-end tracing across services. Always validates it to prevent
 * header injection / log forging.
 */
class RequestId
{
    public const HEADER = 'X-Request-ID';

    public const PATTERN = '/^[a-zA-Z0-9\-_]{1,64}$/';

    public function handle(Request $request, Closure $next): Response
    {
        $incoming = $request->headers->get(self::HEADER);
        $requestId = ($incoming && preg_match(self::PATTERN, $incoming))
            ? $incoming
            : (string) Str::uuid();

        // Bind into container so controllers/jobs can read it
        app()->instance('request_id', $requestId);

        // Add to Log context so every log line includes the ID
        Log::shareContext(['request_id' => $requestId]);

        // Attach to request for downstream middleware
        $request->headers->set(self::HEADER, $requestId);
        $request->attributes->set('request_id', $requestId);

        $response = $next($request);
        $response->headers->set(self::HEADER, $requestId);

        return $response;
    }
}
