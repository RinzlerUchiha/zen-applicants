<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ColorController extends Controller
{
    public static function show()
    {
        $answer = auth()->user()->color;
        if($answer?->wcay_ans){
            $answer->wcay_ans = json_decode($answer->wcay_ans, true);
        }
        return view('pages.color', [
            'answerList' => Color::showAnswerList(),
            'answer' => $answer
        ]);
    }

    public static function store(Request $request)
    {
        try {

            $validated = $request->validate([
                    'set' => 'required|array',
                    // 'set.*' => 'required|array'
                    'set.*' => 'required|integer'
                ],
                [
                    'set.required' => 'Please select atleast 1 from the set',
                    'set.*.required' => 'Please select atleast 1 from the set',
                    'set.array' => 'Invalid Input',
                    // 'set.*.array' => 'Invalid Input'
                    'set.*.integer' => 'Invalid Input'
                ]
            );

            $counts = collect($validated['set'])
            // ->countBy('cat')
            ->countBy('cat')
            ->toArray();

            $color = auth()->user()->color()->firstOrNew([]);

            $color->_1 = ($counts[1] ?? 0);
            $color->_2 = ($counts[2] ?? 0);
            $color->_3 = ($counts[3] ?? 0);
            $color->_4 = ($counts[4] ?? 0);
            $color->wcay_ans = json_encode($validated['set']);
            $color->wcay_dt = now()->format('Y-m-d');
            
            $color->save();

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
