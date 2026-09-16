<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Document;
use App\Models\DocumentProcess;
use App\Models\DocumentRequest;
use Illuminate\Support\Collection;

/**
 * Where an applicant stands on their application-stage documents, from their
 * own point of view: what is on file, HR's check of it, and whether HR is
 * waiting on them. Shared by the documents page and the home page so both
 * say the same thing.
 */
class ApplicantDocumentStatus
{
    /** The types an applicant can send at the application stage. */
    public static function uploadableTypes(): array
    {
        return array_merge(config('documents.required'), config('documents.optional'));
    }

    /**
     * The document-completion run the applicant is in, if any: the active one,
     * otherwise the outcome they ended on. Read-only here — HR starts and ends
     * it, and the only thing the applicant can do is withdraw.
     */
    public static function process(int $appId): ?DocumentProcess
    {
        return DocumentProcess::where('app_id', $appId)
            ->orderByRaw("status = '" . DocumentProcess::ACTIVE . "' DESC")
            ->latest('id')
            ->first();
    }

    /**
     * One entry per application-stage type:
     *   type, label, required, document (?Document), request (?DocumentRequest),
     *   request_for (?string position title), needs_action (bool)
     */
    public static function slots(int $appId): Collection
    {
        $types = self::uploadableTypes();
        $required = config('documents.required');

        $documents = Document::where('app_id', $appId)
            ->whereIn('doc_type', $types)
            ->orderByDesc('id')
            ->get()
            ->unique('doc_type')
            ->keyBy('doc_type');

        $requests = DocumentRequest::where('app_id', $appId)
            ->whereIn('doc_type', $types)
            ->active()
            ->orderByDesc('id')
            ->get()
            ->unique('doc_type')
            ->keyBy('doc_type');

        // Name the position a request was made for, when HR gave one.
        $applicationTitles = Application::whereIn('id', $requests->pluck('application_id')->filter())
            ->get()
            ->mapWithKeys(fn ($application) => [$application->id => $application->jobPosting()?->posting_title]);

        return collect($types)->map(function ($type) use ($documents, $requests, $required, $applicationTitles) {
            $document = $documents->get($type);
            $request = $requests->get($type);

            return [
                'type' => $type,
                'label' => config('documents.types.' . $type),
                'required' => in_array($type, $required, true),
                'document' => $document,
                'request' => $request,
                'request_for' => $request ? $applicationTitles->get($request->application_id) : null,
                // An open request, or a document HR could not accept. A request
                // the applicant has already answered is back with HR, so it is
                // not repeated to them.
                'needs_action' => ($request && $request->status === 'open')
                    || $document?->review_status === 'rejected',
            ];
        })->values();
    }
}
