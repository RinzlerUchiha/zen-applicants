<?php

namespace App\Http\Middleware;

use App\Services\AssessmentAttempts;
use Closure;
use Illuminate\Http\Request;

/**
 * While an aptitude test is started and not yet finished, the assessments
 * area leads back to it: the Assessments list and every other assessment's
 * page redirect to the aptitude test. Its own page is where it is continued,
 * resumed after an interruption (with a new HR code), or taken over from
 * another browser — so recovery works exactly as before.
 *
 * A workflow rule only: nothing is recorded or reported about the applicant.
 */
class AptitudeLock
{
    public function __construct(private AssessmentAttempts $attempts)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $routes = collect(config('application_form.assessments.list'))->mapWithKeys(fn ($d, $key) => [$d['route'] => $key]);
        $current = $routes->get($request->route()?->getName());

        $aptitude = $this->attempts->unfinishedAptitude($user, $current);

        if ($aptitude === null) {
            return $next($request);
        }

        $definition = $this->attempts->definition($aptitude);

        return redirect()->route($definition['route'])
            ->with('error', 'Finish ' . $definition['label'] . ' first — an aptitude test is completed before any other assessment.');
    }
}
