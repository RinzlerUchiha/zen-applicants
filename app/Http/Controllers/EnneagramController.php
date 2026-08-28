<?php

namespace App\Http\Controllers;

use App\Models\Enneagram;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnneagramController extends Controller
{
    public static function show()
    {
        $answer = auth()->user()->enneagram;
        if($answer?->enneagram_ans){
            $answer->enneagram_ans = json_decode($answer->enneagram_ans, true);
        }
        return view('pages.enneagram', [
            'answerList' => Enneagram::showAnswerList(),
            'answer' => $answer
        ]);
    }

    public static function store(Request $request)
    {
        try {

            $validated = $request->validate([
                    'set' => 'required|array',
                    'set.*' => 'required|array',
                    // 'set.*.*' => 'required|string',
                    'set.*.*' => 'required|integer',
                ],
                [
                    'set.required' => 'Please select atleast 1 from the set',
                    'set.*.required' => 'Please select atleast 1 from the set',
                    'set.*.*.required' => 'Please select atleast 1 from the set',
                    'set.array' => 'Invalid Input',
                    'set.*.array' => 'Invalid Input',
                    'set.*.*.integer' => 'Invalid Input',
                ]
            );

            $counts = array_count_values(
                array_merge(...array_map('array_values', $validated['set']))
            );

            $enneagram = auth()->user()->enneagram()->firstOrNew([]);

            $enneagram->{'1_perfectionist'} = ($counts[1] ?? 0);
            $enneagram->{'2_helper'} = ($counts[2] ?? 0);
            $enneagram->{'3_achiever'} = ($counts[3] ?? 0);
            $enneagram->{'4_romantic'} = ($counts[4] ?? 0);
            $enneagram->{'5_observer'} = ($counts[5] ?? 0);
            $enneagram->{'6_questioner'} = ($counts[6] ?? 0);
            $enneagram->{'7_adventurer'} = ($counts[7] ?? 0);
            $enneagram->{'8_asserter'} = ($counts[8] ?? 0);
            $enneagram->{'9_peacemaker'} = ($counts[9] ?? 0);
            $enneagram->enneagram_ans = json_encode($validated['set']);
            $enneagram->enneagram_dt = now()->format('Y-m-d');

            $enneagram->save();

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