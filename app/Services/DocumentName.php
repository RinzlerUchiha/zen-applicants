<?php

namespace App\Services;

/**
 * The standard name of an applicant document:
 *
 *     LAST NAME, FIRST NAME, MIDDLE INITIAL - <Type>.<ext>
 *     e.g. DELA CRUZ, JUAN, P - CV.pdf
 *
 * It is the document's logical name — what HR and the applicant see and what
 * a download is saved as. The file itself stays stored under a random name
 * (FileService::storeDocument()), so this never becomes a guessable path.
 *
 * Set when the document is uploaded, from the name on the applicant's profile
 * at that moment, and stored in tblapp_documents.doc_name. zen-admin reads the
 * stored value; it does not rebuild it.
 */
class DocumentName
{
    public static function make(?string $last, ?string $first, ?string $middle, ?string $suffix, string $type, string $extension): string
    {
        $first = trim(trim((string) $first) . ' ' . trim((string) $suffix));
        $initial = mb_substr(trim((string) $middle), 0, 1);

        $person = implode(', ', array_filter([
            trim((string) $last),
            $first,
            $initial,
        ], fn ($part) => $part !== ''));

        $label = config('documents.file_labels.' . $type)
            ?: config('documents.types.' . $type, $type);

        $name = self::clean(mb_strtoupper($person, 'UTF-8'));
        $name = ($name !== '' ? $name . ' - ' : '') . self::clean($label);

        return $name . '.' . strtolower(preg_replace('/[^A-Za-z0-9]/', '', $extension) ?: 'bin');
    }

    public static function forUser(object $user, string $type, string $extension): string
    {
        return self::make($user->app_lname, $user->app_fname, $user->app_mname, $user->app_suffix ?? null, $type, $extension);
    }

    /** Characters Windows, macOS or a header cannot take are removed. */
    private static function clean(string $text): string
    {
        $text = preg_replace('/[\\\\\/:*?"<>|\x00-\x1F\x7F]+/u', ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text), " .");
    }
}
