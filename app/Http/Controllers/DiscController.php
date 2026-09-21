<?php

namespace App\Http\Controllers;

use App\Models\Disc;
use App\Rules\KnownKeys;
use App\Services\AssessmentAttempts;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DiscController extends Controller
{
    public static function show()
    {
        $attempts = app(AssessmentAttempts::class);
        $exam = $attempts->view(auth()->user(), 'disc', claim: true);

        $answer = $exam->hasResult ? auth()->user()->disc : null;
        if ($answer?->disc_ans) {
            $answer->disc_ans = json_decode($answer->disc_ans, true);
        }
        // Saved answers once finished; the autosaved draft while running.
        $prefill = $answer ? ($answer->disc_ans ?? []) : ($exam->attempt?->draft['set'] ?? []);

        $list = Disc::showAnswerList();

        return view('pages.disc', [
            'answerList' => $list,
            'answer' => $answer,
            'prefill' => $prefill,
            'exam' => $exam,
        ]);
    }

    /**
     * Submit — only while this applicant's attempt may still be written
     * (App\Services\AssessmentAttempts). The saving itself is save(), unchanged.
     */
    public static function store(Request $request)
    {
        return app(AssessmentAttempts::class)->submit(auth()->user(), 'disc', $request, fn (Request $r) => self::save($r));
    }

    /** Validate and store the answers (also used for the time-up submission). */
    public static function save(Request $request)
    {
        try {

            $list = Disc::showAnswerList();

            $validated = $request->validate([
                    'set' => ['required', 'array', new KnownKeys(array_keys($list), complete: true)],
                    // Each set is ranked 4-3-2-1, every rank used once.
                    'set.*' => ['required', 'array', new KnownKeys(['D', 'I', 'S', 'C'], complete: true),
                        fn ($attribute, $value, $fail) => is_array($value) && collect($value)->map(fn ($v) => (int) $v)->sort()->values()->all() === [1, 2, 3, 4] ? null : $fail('Please fill up each set')],
                    'set.*.*' => 'required|integer|between:1,4',
                ],
                [
                    'set.required' => 'Please fill up each set',
                    'set.*.required' => 'Please fill up each set',
                    'set.*.*.required' => 'Please fill up each set',
                    'set.array' => 'Invalid Input',
                    'set.*.array' => 'Invalid Input',
                    // 'set.*.*.array' => 'Invalid Input',
                    'set.*.*.integer' => 'Invalid Input',
                ]
            );

            // foreach ($data as $level => $values) {
            //     foreach ($values as $letter => $info) {
            //         $totals[$letter] += $info['rank'];
            //     }
            // }

            $totals = collect($validated['set'])
            // ->map(fn($group) => collect($group)->map->rank)
            ->reduce(fn($carry, $item) => $carry->mergeRecursive($item), collect())
            ->map(fn($values) => collect($values)->sum())
            ->toArray();

            // auth()->user()->disc()->updateOrCreate(
            //     [],
            //     [
            //         '_d' => ($totals['D'] ?? 0),
            //         '_i' => ($totals['I'] ?? 0),
            //         '_s' => ($totals['S'] ?? 0),
            //         '_c' => ($totals['C'] ?? 0),
            //         'disc_ans' => json_encode($validated['set']),
            //         'disc_dt' => now()->format('Y-m-d')
            //     ]
            // );

            $disc = auth()->user()->disc()->firstOrNew([]);

            $disc->_d = ($totals['D'] ?? 0);
            $disc->_i = ($totals['I'] ?? 0);
            $disc->_s = ($totals['S'] ?? 0);
            $disc->_c = ($totals['C'] ?? 0);
            $disc->disc_ans = json_encode($validated['set']);
            $disc->disc_dt = now()->format('Y-m-d');
            
            $disc->save();

            return response()->json([
                'success' => true,
                'message' => 'Successful!',
            ]);

        } catch (ValidationException $e) {

            $allErrors = array_unique(array_merge(...array_values($e->errors())));

            return response()->json([
                'success' => false,
                // 'error' => 'Validation failed',
                'error' => $allErrors,
                'message' => 'Please check the form and try again.',  // Custom message
                'validation_errors' => $e->errors(),  // Custom error details
            ], 422);  // 422 Unprocessable Entity
        } catch (\Exception $e) {
            // Catch any other general exceptions and send a generic error message
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),  // Optionally, you can send the exception message
            ], 500);  // 500 Internal Server Error
        }

    }
}
