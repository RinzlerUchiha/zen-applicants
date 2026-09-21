<?php

namespace App\Http\Controllers;

use App\Models\BasicMath;
use App\Rules\KnownKeys;
use App\Services\AssessmentAttempts;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BasicMathController extends Controller
{
    public static function show()
    {
        $attempts = app(AssessmentAttempts::class);
        $exam = $attempts->view(auth()->user(), 'basic_math', claim: true);

        $answer = $exam->hasResult ? auth()->user()->basicMath : null;
        if ($answer?->math_ans) {
            $answer->math_ans = json_decode($answer->math_ans, true);
        }
        // Saved answers once finished; the autosaved draft while running.
        $prefill = $answer ? ($answer->math_ans ?? []) : ($exam->attempt?->draft['set'] ?? []);

        $list = BasicMath::showAnswerList();

        // A running attempt sees the questions, and each question's choices,
        // in its own order. Keys are kept, so the answers are the same keys.
        if ($exam->status === 'active') {
            $list = $attempts->order($exam->attempt, $list, 'questions');
            foreach ($list as $i => $item) {
                $list[$i]['answer'] = $attempts->order($exam->attempt, $item['answer'], "choices-$i");
            }
        }

        return view('pages.basic-math', [
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
        return app(AssessmentAttempts::class)->submit(auth()->user(), 'basic_math', $request, fn (Request $r) => self::save($r));
    }

    /** Validate and store the answers (also used for the time-up submission). */
    public static function save(Request $request)
    {
        try {

            $list = BasicMath::showAnswerList();

            $validated = $request->validate([
                    'set' => ['required', 'array', new KnownKeys(array_keys($list))],
                    // Unanswered items are sent as null and simply score nothing.
                    'set.*' => 'nullable|string|in:a,b,c,d'
                ],
                [
                    'set.required' => 'Please select atleast 1 from the set',
                    // 'set.*.required' => 'Please select atleast 1 from the set',
                    'set.array' => 'Invalid Input',
                    'set.*.string' => 'Invalid Input'
                ]
            );

            $vak = auth()->user()->basicMath()->firstOrNew([]);

            $vak->math_ans = json_encode($validated['set']);
            $vak->math_dt = now()->format('Y-m-d');
            
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
