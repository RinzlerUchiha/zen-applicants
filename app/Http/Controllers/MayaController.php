<?php

namespace App\Http\Controllers;

use App\Models\Maya;
use App\Rules\KnownKeys;
use App\Services\AssessmentAttempts;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MayaController extends Controller
{
    public static function show()
    {
        $attempts = app(AssessmentAttempts::class);
        $exam = $attempts->view(auth()->user(), 'maya', claim: true);

        $answer = $exam->hasResult ? auth()->user()->maya : null;
        if ($answer?->maya_ans) {
            $answer->maya_ans = json_decode($answer->maya_ans, true);
        }
        // Saved answers once finished; the autosaved draft while running.
        $prefill = $answer ? ($answer->maya_ans ?? []) : ($exam->attempt?->draft['set'] ?? []);

        $list = Maya::showAnswerList();

        return view('pages.maya', [
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
        return app(AssessmentAttempts::class)->submit(auth()->user(), 'maya', $request, fn (Request $r) => self::save($r));
    }

    /** Validate and store the answers (also used for the time-up submission). */
    public static function save(Request $request)
    {
        try {

            $list = Maya::showAnswerList();
            $options = collect($list)->flatMap(fn ($set, $s) => collect($set)->mapWithKeys(fn ($item, $i) => [$s . $i => $item['options']]))->all();

            $validated = $request->validate([
                    // Keys are set + item ("a1" .. "e12"); each answer must be one
                    // of that item's own options. Unanswered items are null.
                    'set' => ['required', 'array', new KnownKeys(array_keys($options)),
                        fn ($attribute, $value, $fail) => is_array($value) && collect($value)->every(fn ($v, $k) => $v === null || is_scalar($v) && in_array((string) $v, array_map('strval', $options[$k] ?? []), true)) ? null : $fail('Invalid Input')],
                    'set.*' => 'nullable|string'
                ],
                [
                    'set.required' => 'Please select atleast 1 from the set',
                    // 'set.*.required' => 'Please select atleast 1 from the set',
                    'set.array' => 'Invalid Input',
                    'set.*.string' => 'Invalid Input'
                ]
            );

            $vak = auth()->user()->maya()->firstOrNew([]);

            $vak->maya_ans = json_encode($validated['set']);
            $vak->maya_dt = now()->format('Y-m-d');
            
            $vak->save();

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
