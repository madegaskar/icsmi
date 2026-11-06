<?php

namespace App\Http\Middleware;

use App\Services\VisitCounter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Counts successful public page views. Staff areas, forms, and crawlers are left out.
 */
class CountVisits
{
    private const EXCLUDED_PREFIXES = [
        'admin', 'editor', 'library', 'reports', 'media', 'dashboard', 'profile', 'author', 'reviewer',
        'login', 'register', 'logout', 'forgot-password', 'reset-password', 'verify-email', 'confirm-password',
        'submission', 'manuscript-files', 'up', 'storage',
    ];

    private const CRAWLER_PATTERN = '/bot|crawl|spider|slurp|preview|headless/i';

    public function __construct(private readonly VisitCounter $counter) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldCount($request, $response)) {
            $this->counter->record($request);
        }

        return $response;
    }

    private function shouldCount(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || ! $response->isSuccessful() || $request->expectsJson()) {
            return false;
        }

        if (preg_match(self::CRAWLER_PATTERN, (string) $request->userAgent()) === 1) {
            return false;
        }

        $firstSegment = strtolower((string) $request->segment(1));

        return ! in_array($firstSegment, self::EXCLUDED_PREFIXES, true);
    }
}
