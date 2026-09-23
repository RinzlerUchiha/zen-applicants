<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The application documents collected during sign-up, when someone is signing
 * up in order to apply for a job.
 *
 * The account does not exist yet, so a chosen file is held against the
 * visitor's session — under its own folder on the documents disk, never in the
 * applicant's folder — and only becomes a document once the account is
 * created. A form that bounces back (a taken email, say) therefore does not
 * ask for the files again, which a browser cannot repopulate by itself.
 *
 * Anything still held for a session that never finished is left behind as a
 * stray private file; ApplicantDocumentStatus and HR never see it, and it can
 * be swept by deleting folders under the sign-up path.
 */
class SignupDocuments
{
    private const SESSION_KEY = 'signup_documents';
    private const PATH = 'applicant/signup';

    /** The field name each type is uploaded under on the sign-up form. */
    public static function fields(): array
    {
        return collect(ApplicantDocumentStatus::uploadableTypes())
            ->mapWithKeys(fn ($type) => [$type => 'doc_' . $type])
            ->all();
    }

    /** What is held for this session: type => ['file' =>, 'mime' =>, 'size' =>, 'original_name' =>] */
    public static function held(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /** Required types not held yet (config/documents.php decides which). */
    public static function missing(): array
    {
        $held = self::held();

        return collect(config('documents.required'))
            ->reject(fn ($type) => isset($held[$type]))
            ->mapWithKeys(fn ($type) => [$type => config('documents.types.' . $type)])
            ->all();
    }

    /**
     * Take whatever the sign-up form sent, validating it exactly as the
     * Documents page does. Replaces anything held for the same type.
     */
    public static function take(Request $request): void
    {
        $rules = [];
        $messages = [];

        foreach (self::fields() as $type => $field) {
            if (!$request->hasFile($field)) {
                continue;
            }
            $rules[$field] = ['file', 'mimes:' . implode(',', config('documents.extensions')), 'max:' . config('documents.max_size_kb')];
            $label = config('documents.types.' . $type);
            $messages["$field.mimes"] = "Your $label must be a PDF, JPG or PNG file.";
            $messages["$field.max"] = "Your $label is too large. The limit is " . round(config('documents.max_size_kb') / 1024) . ' MB.';
        }

        if (!$rules) {
            return;
        }

        $request->validate($rules, $messages);

        $held = self::held();

        foreach (self::fields() as $type => $field) {
            $file = $request->file($field);
            if (!$file instanceof UploadedFile) {
                continue;
            }

            if (isset($held[$type])) {
                self::forget($held[$type]['file']);
            }

            $stored = FileService::storeDocument($file, self::folder(), config('documents.disk'));
            // 'file' becomes the full key on the disk, not just the name.
            $held[$type] = array_merge($stored, ['file' => self::folder() . '/' . $stored['file']]);
        }

        session([self::SESSION_KEY => $held]);
    }

    /**
     * Turn what is held into the new applicant's documents. Runs once the
     * account exists; each file moves into the applicant's own folder and
     * gets its standard name, exactly as an upload from the Documents page.
     */
    public static function commit(int $appId): void
    {
        $held = self::held();
        if (!$held) {
            return;
        }

        $user = \App\Models\User::find($appId);
        $disk = Storage::disk(config('documents.disk'));
        $folder = config('documents.path') . '/' . $appId;

        foreach ($held as $type => $file) {
            try {
                $name = basename($file['file']);
                $key = $folder . '/' . $name;

                if (!$disk->exists($file['file'])) {
                    continue;
                }

                $disk->move($file['file'], $key);

                \App\Models\Document::create([
                    'app_id' => $appId,
                    'doc_type' => $type,
                    'doc_file' => $key,
                    'doc_label' => null,
                    'doc_original_name' => $file['original_name'],
                    'doc_name' => $user ? DocumentName::forUser($user, $type, pathinfo($name, PATHINFO_EXTENSION)) : $name,
                    'doc_mime' => $file['mime'],
                    'doc_size' => $file['size'],
                    'uploaded_at' => now(),
                    'review_status' => 'pending',
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        session()->forget(self::SESSION_KEY);
    }

    /** Refuse the sign-up when a job is being applied for without its materials. */
    public static function requireForJob(): void
    {
        $missing = self::missing();

        if ($missing) {
            throw ValidationException::withMessages([
                'documents' => ['Add your ' . strtolower(implode(' and ', $missing)) . ' to apply for this position.'],
            ]);
        }
    }

    private static function folder(): string
    {
        return self::PATH . '/' . substr(hash('sha256', (string) session()->getId()), 0, 32);
    }

    private static function forget(string $key): void
    {
        try {
            Storage::disk(config('documents.disk'))->delete($key);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
