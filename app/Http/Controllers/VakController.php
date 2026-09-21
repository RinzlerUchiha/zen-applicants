<?php

namespace App\Http\Controllers;

use App\Models\Vak;
use App\Rules\KnownKeys;
use App\Services\AssessmentAttempts;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VakController extends Controller
{
    public static function show()
    {
        $attempts = app(AssessmentAttempts::class);
        $exam = $attempts->view(auth()->user(), 'vak', claim: true);

        $answer = $exam->hasResult ? auth()->user()->vak : null;
        if ($answer?->vak_ans) {
            $answer->vak_ans = json_decode($answer->vak_ans, true);
        }
        // Saved answers once finished; the autosaved draft while running.
        $prefill = $answer ? ($answer->vak_ans ?? []) : ($exam->attempt?->draft['set'] ?? []);

        $list = Vak::showAnswerList();

        return view('pages.vak', [
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
        return app(AssessmentAttempts::class)->submit(auth()->user(), 'vak', $request, fn (Request $r) => self::save($r));
    }

    /** Validate and store the answers (also used for the time-up submission). */
    public static function save(Request $request)
    {
        try {

            $list = Vak::showAnswerList();

            $validated = $request->validate([
                    'set' => ['required', 'array', new KnownKeys(array_keys($list), complete: true)],
                    'set.*' => 'required|string|in:a,b,c'
                ],
                [
                    'set.required' => 'Please select atleast 1 from the set',
                    'set.*.required' => 'Please select atleast 1 from the set',
                    'set.array' => 'Invalid Input',
                    // 'set.*.array' => 'Invalid Input'
                    'set.*.string' => 'Invalid Input'
                ]
            );

            $counts = collect($validated['set'])
            // ->groupBy('cat')->map->count()
            ->countBy()
            ->toArray();

            $vak = auth()->user()->vak()->firstOrNew([]);

            $vak->_a = ($counts['a'] ?? 0);
            $vak->_b = ($counts['b'] ?? 0);
            $vak->_c = ($counts['c'] ?? 0);
            $vak->vak_ans = json_encode($validated['set']);
            $vak->vak_dt = now()->format('Y-m-d');
            
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
