<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EducationController extends Controller
{
    public static function index()
    {
        $education = auth()->user()->education;

        return view('pages.education', compact('education'));
    }

    public static function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'education-id' => 'nullable|numeric',
                'education-level' => 'required|string',
                'education-degree' => 'nullable|string',
                'education-major' => 'nullable|string',
                'education-school' => 'nullable|string',
                'education-address' => 'nullable|string',
                'education-year-graduated' => 'nullable|numeric',
                'education-curstat' => 'nullable|string'
            ]);

            $validator->setAttributeNames([
                'education-level' => 'Level',
                'education-degree' => 'Degree',
                'education-major' => 'Major',
                'education-school' => 'School',
                'education-address' => 'Address',
                'education-year-graduated' => 'Year graduated',
                'education-curstat' => 'Status',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            $education = auth()->user()->education()->firstOrNew(['educ_id' => $validated['education-id']]);

            $education->educ_level = $validated['education-level'];
            $education->educ_degreetitle = $validated['education-degree'];
            $education->educ_major = $validated['education-major'];
            $education->educ_school = $validated['education-school'];
            $education->educ_schooladd = $validated['education-address'];
            $education->educ_yeargrad = $validated['education-year-graduated'];
            $education->educ_currStatus = $validated['education-curstat'];
            $education->status = 1;

            $education->save();

            return redirect()->route('education.index')->with('success', 'Education info updated');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }

    public static function delete($id)
    {
        try {
            auth()->user()->education()->find($id)->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }
}
