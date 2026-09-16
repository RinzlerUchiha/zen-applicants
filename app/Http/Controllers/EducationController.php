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

            // A school that is added must be complete. Degree and year follow the
            // agreed conditions in config/application_form.php: no degree title
            // for primary or secondary, no graduation year while still enrolled.
            $validator = Validator::make($request->all(), [
                'education-id' => 'nullable|numeric',
                'education-level' => 'required|string|in:Primary,Secondary,Tertiary',
                'education-degree' => 'nullable|required_if:education-level,Tertiary|string|max:50',
                'education-major' => 'nullable|string|max:50',
                'education-school' => 'required|string|max:50',
                'education-address' => 'nullable|string|max:50',
                'education-year-graduated' => 'nullable|required_unless:education-curstat,Currently enrolled|integer|between:1900,2100',
                'education-curstat' => 'required|string|in:Completed,Graduated,Currently enrolled'
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
            // A graduation year means nothing for someone still enrolled.
            $education->educ_yeargrad = $validated['education-curstat'] === 'Currently enrolled'
                ? null
                : $validated['education-year-graduated'];
            $education->educ_currStatus = $validated['education-curstat'];
            $education->status = 1;

            $education->save();

            return redirect()->route('education.index')->with('success', 'Education info updated');
        } catch (ValidationException $e) {
            // Every field the record is missing, against the field itself — not
            // the first one flattened into a single "failed to process" line.
            throw $e;
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
