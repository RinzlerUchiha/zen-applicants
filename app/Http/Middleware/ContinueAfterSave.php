<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Save & continue" for the Application Form's unsaved-changes prompt.
 *
 * The section's normal save runs untouched. Only when it succeeds, and the
 * form carried an _after_save destination, is its redirect swapped for that
 * destination — so the applicant lands where they were going, with the usual
 * success message. A failed save (validation or otherwise) keeps its own
 * redirect back to the section, errors and all.
 *
 * The destination must be on this site: an absolute URL for another host, a
 * protocol-relative "//host" or a non-http scheme is ignored.
 */
class ContinueAfterSave
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $target = $request->input('_after_save');

        if (!is_string($target) || $target === '' || !$response instanceof RedirectResponse) {
            return $response;
        }

        $session = $request->session();
        if ($session->has('errors') || $session->has('error')) {
            return $response;
        }

        return $this->isSameSite($target, $request) ? redirect()->to($target) : $response;
    }

    private function isSameSite(string $target, Request $request): bool
    {
        if (str_starts_with($target, '//') || preg_match('/[\r\n]/', $target)) {
            return false;
        }

        $parts = parse_url($target);

        if ($parts === false) {
            return false;
        }

        if (!isset($parts['host'])) {
            return str_starts_with($target, '/') && !isset($parts['scheme']);
        }

        return in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && strcasecmp($parts['host'], $request->getHost()) === 0;
    }
}
