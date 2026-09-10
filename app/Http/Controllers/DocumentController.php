<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::where('app_id', auth()->user()->app_id)
            ->orderByDesc('uploaded_at')
            ->get();

        $required = collect(config('documents.required', []));
        $optional = collect(config('documents.optional', []));
        $laterStage = collect(config('documents.later_stage', []));

        // Only what is actually being asked for now is offered. Later-stage
        // documents stay defined in config for HR and the eventual 201 file,
        // but showing them here would present the applicant with five items
        // nobody has requested yet.
        $selectable = collect(config('documents.types'))
            ->reject(fn ($label, $key) => $laterStage->contains($key));

        return view('pages.documents', [
            'documents'  => $documents,
            'types'      => $selectable,
            'required'   => $required,
            'optional'   => $optional,
            'otherType'  => config('documents.other_type'),
            'maxSizeKb'  => config('documents.max_size_kb'),
            'extensions' => config('documents.extensions'),
            'submitted'  => $required->filter(fn ($t) => $documents->contains('doc_type', $t))->count(),
        ]);
    }

    public function store(Request $request)
    {
        $otherType = config('documents.other_type');

        $validated = $request->validate(
            [
                'doc_type' => ['required', Rule::in(array_keys(config('documents.types')))],
                // Only the "Other" type carries an applicant-supplied name.
                'doc_label' => ['nullable', 'string', 'max:150', Rule::requiredIf($request->input('doc_type') === $otherType)],
                'doc_file' => [
                    'required',
                    'file',
                    'mimes:' . implode(',', config('documents.extensions')),
                    'max:' . config('documents.max_size_kb'),
                ],
            ],
            [
                'doc_type.required' => 'Please choose a document type.',
                'doc_type.in' => 'That document type is not recognised.',
                'doc_label.required' => 'Please give this document a name.',
                'doc_file.required' => 'Please choose a file to upload.',
                'doc_file.mimes' => 'Only PDF, JPG and PNG files are accepted.',
                'doc_file.max' => 'The file is too large. The limit is ' . round(config('documents.max_size_kb') / 1024) . ' MB.',
            ]
        );

        $appId = auth()->user()->app_id;

        try {
            $stored = FileService::storeDocument(
                $request->file('doc_file'),
                config('documents.path') . '/' . $appId,
                $this->disk()
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('documents.index')
                ->withErrors(['doc_file' => 'That file could not be uploaded. Please try a different file.']);
        }

        Document::create([
            'app_id' => $appId,
            'doc_type' => $validated['doc_type'],
            // A label on any other type would contradict the type's own name.
            'doc_label' => $validated['doc_type'] === $otherType ? $validated['doc_label'] : null,
            'doc_file' => $stored['file'],
            'doc_original_name' => $stored['original_name'],
            'doc_mime' => $stored['mime'],
            'doc_size' => $stored['size'],
            'uploaded_at' => now(),
        ]);

        return redirect()->route('documents.index')->with('success', 'Document uploaded.');
    }

    /**
     * Streams a document inline. Access control is the app_id scope on this
     * lookup — a document belonging to anyone else is simply not found, so
     * there is no filename to guess and nothing to enumerate.
     */
    public function view($id)
    {
        $document = $this->ownedOrFail($id);

        $disk = Storage::disk($this->disk());

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
            'Content-Disposition' => 'inline; filename="' . $this->safeHeaderName($document->doc_original_name) . '"',
            // Never let a browser second-guess the type we just declared.
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, no-store',
        ]);
    }

    public function delete($id)
    {
        $document = $this->ownedOrFail($id);

        // Remove the row first: an orphaned file is recoverable, a row
        // pointing at a deleted file renders as a broken link to the applicant.
        $path = $document->storage_path;
        $document->delete();

        Storage::disk($this->disk())->delete($path);

        return redirect()->route('documents.index')->with('success', 'Document removed.');
    }

    /** Documents are readable only by the applicant who uploaded them. */
    private function ownedOrFail($id): Document
    {
        return Document::where('id', $id)
            ->where('app_id', auth()->user()->app_id)
            ->firstOrFail();
    }

    /** Matches the disk convention already used across this application. */
    private function disk(): string
    {
        return app()->environment('production') ? 's3' : 'public';
    }

    private function safeHeaderName(string $name): string
    {
        return str_replace(['"', "\r", "\n"], '', $name);
    }
}
