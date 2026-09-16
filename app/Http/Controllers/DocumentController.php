<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Services\ApplicantDocumentStatus;
use App\Services\DocumentWithdrawal;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index()
    {
        $appId = auth()->user()->app_id;

        $slots = ApplicantDocumentStatus::slots($appId);

        return view('pages.documents', [
            'required'   => $slots->where('required', true),
            'optional'   => $slots->where('required', false),
            'attention'  => $slots->filter(fn ($slot) => $slot['needs_action']),
            'maxSizeKb'  => config('documents.max_size_kb'),
            'extensions' => config('documents.extensions'),
            // The run HR has started, when there is one: the deadline and how
            // many attempts are left, shared across every document asked for.
            'process'    => ApplicantDocumentStatus::process($appId),
        ]);
    }

    /**
     * Withdraws from the document-completion process.
     *
     * The applicant's own decision to stop, independent of the deadline and the
     * attempt counter. It ends the run; it does not delete anything.
     */
    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'confirm' => 'accepted',
            'note' => 'nullable|string|max:500',
        ], [
            'confirm.accepted' => 'Please confirm that you want to withdraw.',
        ]);

        $process = DocumentWithdrawal::withdraw(auth()->user()->app_id, $validated['note'] ?? null);

        if (!$process) {
            return redirect()->route('documents.index')
                ->withErrors(['withdraw' => 'There is nothing to withdraw from.']);
        }

        return redirect()->route('documents.index')
            ->with('success', 'You have withdrawn. We have kept your details on file.');
    }

    /**
     * Uploads a document, or replaces the one already on file for that type.
     *
     * A replacement is not a new version to manage: the record is updated and
     * HR's check resets to pending, so a decision about the previous file never
     * applies to this one. The previous file is removed once the record points
     * at the new one.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'doc_type' => ['required', Rule::in(ApplicantDocumentStatus::uploadableTypes())],
                'doc_file' => [
                    'required',
                    'file',
                    'mimes:' . implode(',', config('documents.extensions')),
                    'max:' . config('documents.max_size_kb'),
                ],
            ],
            [
                'doc_type.required' => 'Please choose a document type.',
                'doc_type.in' => 'That document type is not accepted at this stage.',
                'doc_file.required' => 'Please choose a file to upload.',
                'doc_file.mimes' => 'Only PDF, JPG and PNG files are accepted.',
                'doc_file.max' => 'The file is too large. The limit is ' . round(config('documents.max_size_kb') / 1024) . ' MB.',
            ]
        );

        $appId = auth()->user()->app_id;
        $type = $validated['doc_type'];
        $disk = config('documents.disk');
        $folder = config('documents.path') . '/' . $appId;

        try {
            $stored = FileService::storeDocument($request->file('doc_file'), $folder, $disk);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('documents.index')
                ->withErrors(['doc_file' => 'That file could not be uploaded. Please try a different file.']);
        }

        $key = $folder . '/' . $stored['file'];

        try {
            $previousPath = DB::transaction(function () use ($appId, $type, $key, $stored) {
                // Serialise uploads per applicant, so two submissions of the
                // same type cannot both create a record.
                User::where('app_id', $appId)->lockForUpdate()->first();

                $document = Document::where('app_id', $appId)
                    ->where('doc_type', $type)
                    ->orderByDesc('id')
                    ->first();

                $previousPath = $document?->storage_path;

                $fields = [
                    'doc_file' => $key,
                    'doc_label' => null,
                    'doc_original_name' => $stored['original_name'],
                    'doc_mime' => $stored['mime'],
                    'doc_size' => $stored['size'],
                    'uploaded_at' => now(),
                    // A new file has not been checked by anyone.
                    'review_status' => 'pending',
                    'review_reason' => null,
                    'review_note' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ];

                if ($document) {
                    $document->update($fields);
                } else {
                    Document::create(['app_id' => $appId, 'doc_type' => $type] + $fields);
                }

                // Answering HR's request hands it back to HR.
                DocumentRequest::where('app_id', $appId)
                    ->where('doc_type', $type)
                    ->active()
                    ->update(['status' => 'submitted', 'submitted_at' => now()]);

                return $previousPath;
            });
        } catch (\Throwable $e) {
            report($e);
            $this->deleteQuietly($disk, $key);

            return redirect()->route('documents.index')
                ->withErrors(['doc_file' => 'That file could not be saved. Please try again.']);
        }

        if ($previousPath && $previousPath !== $key) {
            $this->deleteQuietly($disk, $previousPath);
        }

        return redirect()->route('documents.index')->with(
            'success',
            $previousPath ? 'Document replaced. HR will check the new file.' : 'Document uploaded.'
        );
    }

    /**
     * Streams a document inline. Access control is the app_id scope on this
     * lookup — a document belonging to anyone else is simply not found, so
     * there is no filename to guess and nothing to enumerate.
     */
    public function view($id)
    {
        $document = Document::where('id', $id)
            ->where('app_id', auth()->user()->app_id)
            ->firstOrFail();

        $disk = Storage::disk(config('documents.disk'));

        if (!$disk->exists($document->storage_path)) {
            abort(404);
        }

        $stream = $disk->readStream($document->storage_path);

        if (!$stream) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            try {
                if (is_resource($stream)) {
                    fpassthru($stream);
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, [
            'Content-Type' => $document->doc_mime,
            // The applicant's own filename is only ever a header value, never
            // a path. Quotes and newlines are stripped so it cannot break out
            // of the header or inject another one.
            'Content-Disposition' => 'inline; filename="' . str_replace(['"', "\r", "\n"], '', $document->doc_original_name) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, no-store',
        ]);
    }

    /** Clean-up only: a leftover private file is harmless, a failed request is not. */
    private function deleteQuietly(string $disk, string $path): void
    {
        try {
            Storage::disk($disk)->delete($path);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
