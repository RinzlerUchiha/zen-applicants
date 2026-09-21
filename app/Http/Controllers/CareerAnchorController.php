<?php

namespace App\Http\Controllers;

use App\Models\CareerAnchor;
use App\Rules\KnownKeys;
use App\Services\AssessmentAttempts;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CareerAnchorController extends Controller
{
    public static function show()
    {
        $attempts = app(AssessmentAttempts::class);
        $exam = $attempts->view(auth()->user(), 'career_anchors', claim: true);

        $answer = $exam->hasResult ? auth()->user()->careerAnchor : null;
        if ($answer?->career_ans) {
            $answer->career_ans = json_decode($answer->career_ans, true);
        }
        if ($answer?->career_highest) {
            $answer->career_highest = json_decode($answer->career_highest, true);
        }
        $source = $answer
            ? ['set' => $answer->career_ans ?? [], 'highest' => $answer->career_highest ?? []]
            : ($exam->attempt?->draft ?? []);
        $highest = $source['highest'] ?? [];
        $prefill = [
            'rate' => collect($source['set'] ?? [])->map(fn ($v, $k) => isset($highest[$k]) ? (int) $v - 4 : $v)->all(),
            'highest' => $highest,
        ];

        $list = CareerAnchor::showAnswerList();

        return view('pages.career-anchors', [
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
        return app(AssessmentAttempts::class)->submit(auth()->user(), 'career_anchors', $request, fn (Request $r) => self::save($r));
    }

    /** Validate and store the answers (also used for the time-up submission). */
    public static function save(Request $request)
    {
        try {

            $list = CareerAnchor::showAnswerList();

            $validated = $request->validate([
                    'set' => ['required', 'array', new KnownKeys(array_keys($list), complete: true)],
                    // A rating of 1-6, plus 4 on the three items marked highest.
                    'set.*' => 'required|integer|between:1,10',
                    'highest' => ['required', 'array', 'size:3', new KnownKeys(array_keys($list)),
                        fn ($attribute, $value, $fail) => is_array($value) && collect($value)->every(fn ($v, $k) => (int) $v === (int) ($request->input("set.$k")) && (int) $v > 4) ? null : $fail('Please check the 3 highest items that seem most true for you')],
                    'highest.*' => 'required|integer',
                ],
                [
                    'set.required' => 'Please fill up each item',
                    'set.*.required' => 'Please fill up each item',
                    'set.array' => 'Invalid Input',
                    'set.*.integer' => 'Invalid Input',

                    'highest.required' => 'Please fill up each item',
                    'highest.*.required' => 'Please fill up each item',
                    'highest.array' => 'Invalid Input',
                    'highest.*.integer' => 'Invalid Input',
                ]
            );

            // $max = max($validated['set']);
            // $highest = array_filter($validated['set'], fn ($value) => $value === $max);

            $career = auth()->user()->careerAnchor()->firstOrNew([]);

            $career->career_ans = json_encode($validated['set']);
            $career->career_highest = json_encode($validated['highest']);
            $career->career_dt = now()->format('Y-m-d');
            
            $career->save();

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
