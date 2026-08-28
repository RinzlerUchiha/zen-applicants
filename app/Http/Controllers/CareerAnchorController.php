<?php

namespace App\Http\Controllers;

use App\Models\CareerAnchor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CareerAnchorController extends Controller
{
    public static function show()
    {
        $answer = auth()->user()->careerAnchor;
        if($answer?->career_ans){
            $answer->career_ans = json_decode($answer->career_ans, true);
        }
        if($answer?->career_highest){
            $answer->career_highest = json_decode($answer->career_highest, true);
        }
        return view('pages.career-anchors', [
            'answerList' => CareerAnchor::showAnswerList(),
            'answer' => $answer
        ]);
    }

    public static function store(Request $request)
    {
        try {

            $validated = $request->validate([
                    'set' => 'required|array',
                    'set.*' => 'required|integer',
                    'highest' => 'required|array',
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
