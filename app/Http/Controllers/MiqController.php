<?php

namespace App\Http\Controllers;

use App\Models\Miq;
use App\Rules\KnownKeys;
use App\Services\AssessmentAttempts;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MiqController extends Controller
{
    public static function show()
    {
        $attempts = app(AssessmentAttempts::class);
        $exam = $attempts->view(auth()->user(), 'miq', claim: true);

        $answer = $exam->hasResult ? auth()->user()->miq : null;
        if ($answer?->miq_ans) {
            $answer->miq_ans = json_decode($answer->miq_ans, true);
        }
        // Saved answers once finished; the autosaved draft while running.
        $prefill = $answer ? ($answer->miq_ans ?? []) : array_keys($exam->attempt?->draft['set'] ?? []);

        $list = Miq::showAnswerList();

        return view('pages.miq', [
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
        return app(AssessmentAttempts::class)->submit(auth()->user(), 'miq', $request, fn (Request $r) => self::save($r));
    }

    /** Validate and store the answers (also used for the time-up submission). */
    public static function save(Request $request)
    {
        try {

            $list = Miq::showAnswerList();

            $validated = $request->validate([
                    'set' => ['required', 'array', new KnownKeys(array_keys($list))],
                    'set.*' => 'required|array'
                ],
                [
                    'set.required' => 'Please select atleast 1 from the set',
                    'set.*.required' => 'Please select atleast 1 from the set',
                    'set.array' => 'Invalid Input',
                    'set.*.array' => 'Invalid Input'
                ]
            );

            // The category comes from the question list, not from the request.
            $counts = collect(array_keys($validated['set']))->map(fn ($k) => $list[$k]['cat'])->countBy()->toArray();

            // auth()->user()->miq()->updateOrCreate(
            //     [],
            //     [
            //         '_1' => ($counts[1] ?? 0),
            //         '_2' => ($counts[2] ?? 0),
            //         '_3' => ($counts[3] ?? 0),
            //         '_4' => ($counts[4] ?? 0),
            //         '_5' => ($counts[5] ?? 0),
            //         '_6' => ($counts[6] ?? 0),
            //         '_7' => ($counts[7] ?? 0),
            //         '_8' => ($counts[8] ?? 0),
            //         'miq_ans' => json_encode($validated['set']),
            //         'miq_dt' => now()->format('Y-m-d')
            //     ]
            // );

            $miq = auth()->user()->miq()->firstOrNew([]);

            $miq->_1 = ($counts[1] ?? 0);
            $miq->_2 = ($counts[2] ?? 0);
            $miq->_3 = ($counts[3] ?? 0);
            $miq->_4 = ($counts[4] ?? 0);
            $miq->_5 = ($counts[5] ?? 0);
            $miq->_6 = ($counts[6] ?? 0);
            $miq->_7 = ($counts[7] ?? 0);
            $miq->_8 = ($counts[8] ?? 0);
            $miq->miq_ans = json_encode(array_keys($validated['set']));
            $miq->miq_dt = now()->format('Y-m-d');
            
            $miq->save();

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
