<?php

namespace App\Http\Controllers;

use App\Models\WhyIWork;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WhyIWorkController extends Controller
{
    public static function show()
    {
        $answer = auth()->user()->whyIWork;
        return view('pages.why-i-work', [
            'answerList' => WhyIWork::showAnswerList(),
            'answer' => $answer
        ]);
    }

    public static function store(Request $request)
    {
        try {

            $validated = $request->validate([
                    'set' => 'required|array',
                    // 'set.*' => 'required|array',
                    'set.*' => 'required|integer',
                ],
                [
                    'set.required' => 'Please fill up each set',
                    'set.*.required' => 'Please fill up each set',
                    'set.array' => 'Invalid Input',
                    // 'set.*.array' => 'Invalid Input',
                    'set.*.integer' => 'Invalid Input',
                ]
            );

            $whyIWork = auth()->user()->whyIWork()->firstOrNew([]);

            $whyIWork->outcome_1 = ($validated['set'][1] ?? null);
            $whyIWork->outcome_2 = ($validated['set'][2] ?? null);
            $whyIWork->outcome_3 = ($validated['set'][3] ?? null);
            $whyIWork->outcome_4 = ($validated['set'][4] ?? null);
            $whyIWork->outcome_5 = ($validated['set'][5] ?? null);
            $whyIWork->outcome_6 = ($validated['set'][6] ?? null);
            $whyIWork->outcome_7 = ($validated['set'][7] ?? null);
            $whyIWork->outcome_8 = ($validated['set'][8] ?? null);
            $whyIWork->outcome_9 = ($validated['set'][9] ?? null);
            $whyIWork->outcome_10 = ($validated['set'][10] ?? null);
            $whyIWork->outcome_11 = ($validated['set'][11] ?? null);
            $whyIWork->outcome_12 = ($validated['set'][12] ?? null);
            $whyIWork->wiw_dt = now()->format('Y-m-d');
            
            $whyIWork->save();

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
