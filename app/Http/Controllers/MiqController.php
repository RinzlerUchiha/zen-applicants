<?php

namespace App\Http\Controllers;

use App\Models\Miq;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MiqController extends Controller
{
    public static function show()
    {
        $answer = auth()->user()->miq;
        if($answer?->miq_ans){
            $answer->miq_ans = json_decode($answer->miq_ans, true);
        }
        return view('pages.miq', [
            'answerList' => Miq::showAnswerList(),
            'answer' => $answer
        ]);
    }

    public static function store(Request $request)
    {
        try {

            $validated = $request->validate([
                    'set' => 'required|array',
                    'set.*' => 'required|array'
                ],
                [
                    'set.required' => 'Please select atleast 1 from the set',
                    'set.*.required' => 'Please select atleast 1 from the set',
                    'set.array' => 'Invalid Input',
                    'set.*.array' => 'Invalid Input'
                ]
            );

            $counts = collect($validated['set'])->countBy('cat')->toArray();

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
