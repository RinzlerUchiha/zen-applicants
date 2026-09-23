<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;

/**
 * What an application must come with: the application-stage documents
 * (config/documents.php) — a CV/résumé and a 2x2 picture required, a cover
 * letter optional. Later-stage documents (PSA, NBI, TOR, COE, IDs) are never
 * asked for here; they stay with HR's document-request workflow.
 *
 * The documents are the applicant's own and count for every application, so
 * once they are on file every later application only needs a confirmation.
 */
class ApplicationMaterials
{
    /**
     * Required types not on file. A document HR has asked to be replaced does
     * not count — as on the home page, it is still something left to send.
     *
     * @return array<string, string> type => label
     */
    public static function missing(int $appId): array
    {
        return ApplicantDocumentStatus::slots($appId)
            ->filter(fn ($slot) => $slot['required']
                && (!$slot['document'] || $slot['document']->review_status === 'rejected'))
            ->mapWithKeys(fn ($slot) => [$slot['type'] => $slot['label']])
            ->all();
    }

    public static function ready(int $appId): bool
    {
        return self::missing($appId) === [];
    }

    /**
     * After signing in or signing up from a job posting: apply straight away
     * when the materials are already on file (as before), otherwise take the
     * applicant to the posting's apply step to add them.
     */
    public static function continueTo(int $appId, int $postingId): RedirectResponse
    {
        if (!self::ready($appId)) {
            return redirect()->route('careers.apply.form', $postingId)
                ->with('info', 'One more step: add your CV and 2x2 picture, then submit your application.');
        }

        $result = JobApplicationService::apply($appId, $postingId);

        return redirect()
            ->route('applications.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
