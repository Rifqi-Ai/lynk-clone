<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Compress text responses with gzip.
 *
 * Skipped when:
 * - Response is already encoded (Content-Encoding header set)
 * - Content-Type is not text (HTML, CSS, JS, JSON, XML)
 * - Client did not send Accept-Encoding: gzip
 * - Body is smaller than 1024 bytes (compression overhead > benefit)
 *
 * On success, sets:
 * - Content-Encoding: gzip
 * - Content-Length: <compressed size>
 * - Vary: Accept-Encoding (so caches/CDNs store separate gzipped + plain copies)
 */
class CompressResponse
{
    /** Minimum response size in bytes before we bother compressing. */
    private const MIN_BYTES = 1024;

    /** Content-Type prefixes that we will compress. */
    private const COMPRESSIBLE_PATTERN = '/^(text\/|application\/(json|javascript|xml|ld\+json|manifest\+json|x-javascript))/i';

    /** Compression level: 6 is the default zlib level (good speed/ratio balance). */
    private const GZIP_LEVEL = 6;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Already encoded — don't double-encode.
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        // Only compress text-ish content types.
        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! preg_match(self::COMPRESSIBLE_PATTERN, $contentType)) {
            return $response;
        }

        // Client must accept gzip. Real browsers always send this;
        // legacy clients without gzip support get uncompressed bytes.
        $acceptEncoding = (string) $request->headers->get('Accept-Encoding', '');
        if (! str_contains($acceptEncoding, 'gzip')) {
            return $response;
        }

        // Too small to benefit.
        $content = $response->getContent();
        if ($content === false || strlen($content) < self::MIN_BYTES) {
            return $response;
        }

        // Compress.
        $compressed = gzencode($content, self::GZIP_LEVEL);
        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', (string) strlen($compressed));
        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }
}
