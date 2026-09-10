<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Evaluates how far an applicant has got with their Application Form.
 *
 * Only the 'apply' tier is scored. Fields marked 'for201' are collected and
 * stored but never block an application — the Applicant Profile is persistent
 * and becomes the foundation of the 201 record, so a field being optional now
 * is a statement about timing, not importance.
 *
 * All rules come from config/application_form.php. Nothing is hardcoded here
 * except the mechanics of checking them.
 */
class ApplicationCompletenessService
{
    public function __construct(private User $user)
    {
    }

    public static function for(User $user): self
    {
        return new self($user);
    }

    /**
     * Per-section state, keyed by the config's section key.
     *
     * @return array<string, array{label:string, route:string, blocking:bool,
     *         complete:bool, started:bool, missing:array<string>, rows:int}>
     */
    public function sections(): array
    {
        $out = [];

        foreach (config('application_form.sections') as $key => $section) {
            $out[$key] = $section['type'] === 'single'
                ? $this->evaluateSingle($key, $section)
                : $this->evaluateRepeating($key, $section);
        }

        return $out;
    }

    /**
     * Percentage across the blocking sections only.
     *
     * Optional sections are excluded entirely rather than counted as complete —
     * including them would inflate the figure and make the bar move for work
     * that was never required.
     */
    public function percentage(): int
    {
        $blocking = array_filter($this->sections(), fn ($s) => $s['blocking']);

        if ($blocking === []) {
            return 100;
        }

        $done = count(array_filter($blocking, fn ($s) => $s['complete']));

        return (int) round(($done / count($blocking)) * 100);
    }

    /** @return array{done:int, total:int} */
    public function blockingCounts(): array
    {
        $blocking = array_filter($this->sections(), fn ($s) => $s['blocking']);

        return [
            'done'  => count(array_filter($blocking, fn ($s) => $s['complete'])),
            'total' => count($blocking),
        ];
    }

    public function isComplete(): bool
    {
        $counts = $this->blockingCounts();

        return $counts['done'] === $counts['total'];
    }

    /**
     * The first incomplete blocking section — what the applicant should do next.
     * Returns null once every blocking section is done.
     */
    public function nextSection(): ?array
    {
        foreach ($this->sections() as $key => $section) {
            if ($section['blocking'] && !$section['complete']) {
                return $section + ['key' => $key];
            }
        }

        return null;
    }

    /* ===================================================================
     * Single-row sections (Personal only)
     * =================================================================== */

    private function evaluateSingle(string $key, array $section): array
    {
        $missing = [];

        foreach ($section['fields'] as $field => $rules) {
            if ($rules['apply'] === 'required' && $this->blank($this->user->{$field} ?? null)) {
                $missing[] = $rules['label'];
            }
        }

        // Address is a separate table but belongs to this section.
        if (isset($section['address'])) {
            $address = DB::table('tblapp_address')->where('app_id', $this->user->app_id)->first();

            foreach ($section['address']['required'] as $column) {
                if (!$address || $this->blank($address->{$column} ?? null)) {
                    $missing[] = 'Address';
                    break;
                }
            }
        }

        return $this->result($section, count($missing) === 0, $missing, 1, $this->personalStarted());
    }

    private function personalStarted(): bool
    {
        return !$this->blank($this->user->app_lname) || !$this->blank($this->user->app_fname);
    }

    /* ===================================================================
     * Repeating sections
     * =================================================================== */

    private function evaluateRepeating(string $key, array $section): array
    {
        $rows = $this->rowsFor($key);
        $count = $rows->count();

        // "This is my first job" and friends: an explicit declaration stands
        // in for rows the applicant genuinely has none of.
        if (!empty($section['skip_flag']) && $this->user->{$section['skip_flag']}) {
            return $this->result($section, true, [], 0, true);
        }

        $minRows = $section['min_rows'] ?? 0;

        // Optional sections with nothing in them are complete. An empty list
        // carries no ambiguity, so no declaration tick is asked for.
        if ($count === 0) {
            $complete = $minRows === 0;

            return $this->result(
                $section,
                $complete,
                $complete ? [] : ['At least one entry'],
                0,
                false
            );
        }

        $missing = [];

        // Every row must be fully filled — a half-entered second employer
        // leaves the section incomplete. That is the strict rule working.
        foreach ($rows as $index => $row) {
            foreach ($section['fields'] as $field => $rules) {
                if (!$this->fieldRequiredOnRow($field, $rules, $section, $row)) {
                    continue;
                }

                if ($this->blank($row->{$field} ?? null)) {
                    $missing[] = $count > 1
                        ? sprintf('%s (entry %d)', $rules['label'], $index + 1)
                        : $rules['label'];
                }
            }

            // "At least one of these" groups — a listed type OR a free-text one.
            foreach ($section['any_of'] ?? [] as $group) {
                $answered = false;

                foreach ($group['fields'] as $field) {
                    if (!$this->blank($row->{$field} ?? null)) {
                        $answered = true;
                        break;
                    }
                }

                if (!$answered) {
                    $missing[] = $count > 1
                        ? sprintf('%s (entry %d)', $group['label'], $index + 1)
                        : $group['label'];
                }
            }
        }

        // Married applicants must declare a spouse. Widowed and Separated are
        // deliberately excluded — see the config.
        if (!empty($section['requires_spouse_when_civil_status_in'])
            && $this->civilStatusRequiresSpouse($section['requires_spouse_when_civil_status_in'])
            && !$this->hasSpouseRow($rows)) {
            $missing[] = 'A spouse entry';
        }

        return $this->result($section, count($missing) === 0, $missing, $count, true);
    }

    /**
     * Resolves whether a field is required for one specific row, applying the
     * section's conditions.
     */
    private function fieldRequiredOnRow(string $field, array $rules, array $section, object $row): bool
    {
        if ($rules['apply'] === 'required') {
            return true;
        }

        if ($rules['apply'] !== 'conditional') {
            return false;
        }

        $condition = $section['conditions'][$field] ?? null;

        if (!$condition) {
            return true;
        }

        // Never required — kept as conditional so the intent stays visible.
        if (!empty($condition['exempt_always'])) {
            return false;
        }

        // Exempt when the row carries a declaration flag ("I currently work here").
        if (!empty($condition['exempt_when_flag'])) {
            return empty($row->{$condition['exempt_when_flag']});
        }

        // Exempt when another field on the row holds one of the listed values.
        if (!empty($condition['exempt_when'])) {
            foreach ($condition['exempt_when'] as $otherField => $values) {
                $actual = mb_strtolower(trim((string) ($row->{$otherField} ?? '')));

                foreach ($values as $value) {
                    if ($actual !== '' && str_contains($actual, mb_strtolower($value))) {
                        return false;
                    }
                }
            }

            return true;
        }

        // Required only when a companion field was answered "Other".
        if (!empty($condition['required_when_other'])) {
            $companion = mb_strtolower(trim((string) ($row->{$condition['required_when_other']} ?? '')));

            return str_contains($companion, 'other');
        }

        return true;
    }

    private function civilStatusRequiresSpouse(array $statuses): bool
    {
        $status = mb_strtolower(trim((string) $this->user->app_cstatus));

        return in_array($status, array_map('mb_strtolower', $statuses), true);
    }

    private function hasSpouseRow($rows): bool
    {
        foreach ($rows as $row) {
            if (str_contains(mb_strtolower((string) ($row->fam_relationship ?? '')), 'spouse')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rows for a repeating section. Query builder rather than the Eloquent
     * relations so a section can be evaluated without every model needing a
     * matching accessor.
     */
    private function rowsFor(string $key)
    {
        $tables = [
            'family'      => 'tblapp_family',
            'education'   => 'tblapp_education',
            'employment'  => 'tblapp_employment',
            'skills'      => 'tblapp_skills',
            'eligibility' => 'tblapp_eligibility',
            'certificate' => 'tblapp_certificate',
            'reference'   => 'tblapp_reference',
        ];

        if (!isset($tables[$key])) {
            return collect();
        }

        return DB::table($tables[$key])->where('app_id', $this->user->app_id)->get();
    }

    /* =================================================================== */

    private function result(array $section, bool $complete, array $missing, int $rows, bool $started): array
    {
        return [
            'label'    => $section['label'],
            'route'    => $section['route'],
            'blocking' => $section['blocking'],
            'complete' => $complete,
            'started'  => $started,
            'missing'  => $missing,
            'rows'     => $rows,
        ];
    }

    /** Treats '', null and whitespace as unfilled. 0 and '0' count as answers. */
    private function blank($value): bool
    {
        if ($value === null) {
            return true;
        }

        return trim((string) $value) === '';
    }
}
