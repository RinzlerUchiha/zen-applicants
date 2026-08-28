<?php

namespace App\Http\Controllers;

use App\Models\Maya;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MayaController extends Controller
{
    public static function show()
    {
        $answer = auth()->user()->maya;
        if($answer?->maya_ans){
            $answer->maya_ans = json_decode($answer->maya_ans, true);
        }
        return view('pages.maya', [
            'answerList' => Maya::showAnswerList(),
            'answer' => $answer
        ]);
    }

    public static function store(Request $request)
    {
        try {

            $validated = $request->validate([
                    'set' => 'required|array',
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
