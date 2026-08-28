<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EmploymentRecController extends Controller
{
    public static function index()
    {
        $employment = auth()->user()->employmentRec;

        return view('pages.employment', compact('employment'));
    }

    public static function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'employment-id' => 'nullable|numeric',
                'employment-start-date' => 'required|date',
                'employment-end-date' => 'required|date',
                'employment-company' => 'nullable|string',
                'employment-address' => 'nullable|string',
                'employment-position' => 'nullable|string',
                'employment-contact' => 'nullable|string',
                'employment-supervisor' => 'nullable|string',
                'employment-reason' => 'nullable|string'
            ]);

            $validator->setAttributeNames([
                'employment-start-date' => 'Start Date',
                'employment-end-date' => 'End Date',
                'employment-company' => 'Company',
                'employment-address' => 'Address',
                'employment-position' => 'Position',
                'employment-contact' => 'Contact',
                'employment-supervisor' => 'Supervisor',
                'employment-reason' => 'Reason'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            $employmentRec = auth()->user()->employmentRec()->firstOrNew(['empl_id' => $validated['employment-id']]);

            $employmentRec->empl_from = $validated['employment-start-date'];
            $employmentRec->empl_to = $validated['employment-end-date'];
            $employmentRec->empl_company = $validated['employment-company'];
            $employmentRec->empl_address = $validated['employment-address'];
            $employmentRec->empl_position = $validated['employment-position'];
            $employmentRec->empl_contact = $validated['employment-contact'];
            $employmentRec->empl_supervisor = $validated['employment-supervisor'];
            $employmentRec->empl_reason = $validated['employment-reason'];
            $employmentRec->empl_timestamp = now();

            $employmentRec->save();

            return redirect()->route('employment.index')->with('success', 'Employment info updated');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }

    public static function delete($id)
    {
        try {
            auth()->user()->employmentRec()->find($id)->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }
}
