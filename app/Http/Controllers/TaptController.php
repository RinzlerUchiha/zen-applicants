<?php

namespace App\Http\Controllers;

use App\Models\Tapt;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaptController extends Controller
{
    public static function show()
    {
        $answer = auth()->user()->tapt;
        if($answer?->tapt_ans){
            $answer->tapt_ans = json_decode($answer->tapt_ans, true);
        }
        return view('pages.tapt', [
            'answerList' => Tapt::showAnswerList(),
            'answer' => $answer
        ]);
    }

    public static function store(Request $request)
    {
        try {

            $validated = $request->validate([
                    'set' => 'required|array',
                    'set.e_i' => 'required|array',
                    'set.s_n' => 'required|array',
                    'set.t_f' => 'required|array',
                    'set.j_p' => 'required|array',
                    'set.*.*' => 'required|string',
                ],
                [
                    'set.required' => 'Please fill up each set',
                    'set.e_i.required' => 'Please fill up each set',
                    'set.s_n.required' => 'Please fill up each set',
                    'set.t_f.required' => 'Please fill up each set',
                    'set.j_p.required' => 'Please fill up each set',
                    'set.*.*.required' => 'Please fill up each set',
                    'set.array' => 'Invalid Input',
                    'set.*.array' => 'Invalid Input',
                    'set.*.*.string' => 'Invalid Input',
                ]
            );

            $groups = ['e_i', 's_n', 't_f', 'j_p'];

            $counts = collect($groups)->mapWithKeys(function ($group) use ($validated) {
                $counted = collect($validated['set'][$group])
                // ->pluck(0)
                ->countBy();

                $keyOfMaxValue = $counted->max();
                $key = $counted->search($keyOfMaxValue); 

                return [
                    $group => $key
                ];
            })->toArray();

            $tapt = auth()->user()->tapt()->firstOrNew([]);

            $tapt->e_i = ($counts['e_i'] ?? '');
            $tapt->s_n = ($counts['s_n'] ?? '');
            $tapt->t_f = ($counts['t_f'] ?? '');
            $tapt->j_p = ($counts['j_p'] ?? '');
            $tapt->tapt_ans = json_encode($validated['set']);
            $tapt->tapt_dt = now()->format('Y-m-d');

            $tapt->save();

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
