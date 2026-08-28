<?php

namespace App\Http\Controllers;

use App\Models\Disc;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DiscController extends Controller
{
    public static function show()
    {
        $answer = auth()->user()->disc;
        if($answer?->disc_ans){
            $answer->disc_ans = json_decode($answer->disc_ans, true);
        }
        return view('pages.disc', [
            'answerList' => Disc::showAnswerList(),
            'answer' => $answer
        ]);
    }

    public static function store(Request $request)
    {
        try {

            $validated = $request->validate([
                    'set' => 'required|array',
                    'set.*' => 'required|array',
                    // 'set.*.*' => 'required|array',
                    'set.*.*' => 'required|integer',
                ],
                [
                    'set.required' => 'Please fill up each set',
                    'set.*.required' => 'Please fill up each set',
                    'set.*.*.required' => 'Please fill up each set',
                    'set.array' => 'Invalid Input',
                    'set.*.array' => 'Invalid Input',
                    // 'set.*.*.array' => 'Invalid Input',
                    'set.*.*.integer' => 'Invalid Input',
                ]
            );

            // foreach ($data as $level => $values) {
            //     foreach ($values as $letter => $info) {
            //         $totals[$letter] += $info['rank'];
            //     }
            // }

            $totals = collect($validated['set'])
            // ->map(fn($group) => collect($group)->map->rank)
            ->reduce(fn($carry, $item) => $carry->mergeRecursive($item), collect())
            ->map(fn($values) => collect($values)->sum())
            ->toArray();

            // auth()->user()->disc()->updateOrCreate(
            //     [],
            //     [
            //         '_d' => ($totals['D'] ?? 0),
            //         '_i' => ($totals['I'] ?? 0),
            //         '_s' => ($totals['S'] ?? 0),
            //         '_c' => ($totals['C'] ?? 0),
            //         'disc_ans' => json_encode($validated['set']),
            //         'disc_dt' => now()->format('Y-m-d')
            //     ]
            // );

            $disc = auth()->user()->disc()->firstOrNew([]);

            $disc->_d = ($totals['D'] ?? 0);
            $disc->_i = ($totals['I'] ?? 0);
            $disc->_s = ($totals['S'] ?? 0);
            $disc->_c = ($totals['C'] ?? 0);
            $disc->disc_ans = json_encode($validated['set']);
            $disc->disc_dt = now()->format('Y-m-d');
            
            $disc->save();

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
