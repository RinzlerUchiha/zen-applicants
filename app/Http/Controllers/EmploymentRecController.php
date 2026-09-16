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

            // The employment section can be empty (a first job), but a job that
            // is added must be complete. End date and reason for leaving do not
            // apply to a job the applicant still holds. Limits match the column
            // widths, so an over-long value is a clear message, not a database error.
            $validator = Validator::make($request->all(), [
                'employment-id' => 'nullable|numeric',
                'employment-current' => 'nullable|boolean',
                'employment-start-date' => 'required|date|before_or_equal:today',
                'employment-end-date' => 'nullable|required_unless:employment-current,1|date|after_or_equal:employment-start-date',
                'employment-company' => 'required|string|max:50',
                'employment-address' => 'nullable|string',
                'employment-position' => 'required|string|max:20',
                'employment-contact' => 'nullable|string|max:20',
                'employment-supervisor' => 'nullable|string|max:20',
                'employment-reason' => 'nullable|required_unless:employment-current,1|string|max:100'
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

            $validator->setCustomMessages([
                'employment-end-date.required_unless' => 'Please enter the end date, or tick "I currently work here".',
                'employment-reason.required_unless' => 'Please enter the reason for leaving, or tick "I currently work here".',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();
            $isCurrent = !empty($validated['employment-current']);

            $employmentRec = auth()->user()->employmentRec()->firstOrNew(['empl_id' => $validated['employment-id']]);

            $employmentRec->empl_from = $validated['employment-start-date'];
            $employmentRec->empl_to = $isCurrent ? null : $validated['employment-end-date'];
            $employmentRec->empl_is_current = $isCurrent ? 1 : 0;
            $employmentRec->empl_company = $validated['employment-company'];
            $employmentRec->empl_position = $validated['employment-position'];
            $employmentRec->empl_contact = $validated['employment-contact'];

            // These columns are NOT NULL in tblapp_employment. Salary is not asked
            // for at all, and address, supervisor and reason can be blank, so they
            // are stored empty rather than null. Without this, every save failed.
            $employmentRec->empl_address = $validated['employment-address'] ?? '';
            $employmentRec->empl_supervisor = $validated['employment-supervisor'] ?? '';
            $employmentRec->empl_reason = $isCurrent ? '' : ($validated['employment-reason'] ?? '');
            if (!$employmentRec->exists) {
                $employmentRec->empl_salary = '';
            }

            $employmentRec->empl_timestamp = now();

            $employmentRec->save();

            // Having added a job, "this is my first job" no longer applies.
            $user = auth()->user();
            if ($user->app_no_work_experience) {
                $user->app_no_work_experience = 0;
                $user->save();
            }

            return redirect()->route('employment.index')->with('success', 'Employment info updated');
        } catch (ValidationException $e) {
            // Every field the record is missing, against the field itself — not
            // the first one flattened into a single "failed to process" line.
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }

    /**
     * "This is my first job": an applicant with no work history says so, and
     * the employment section counts as complete (see the skip_flag in
     * config/application_form.php). Only possible while no job is recorded.
     */
    public static function setFirstJob(Request $request)
    {
        $firstJob = $request->boolean('first-job');
        $user = auth()->user();

        if ($firstJob && $user->employmentRec()->exists()) {
            return redirect()->route('employment.index')
                ->withErrors(['error' => 'You already have a job recorded. Remove it first if this is your first job.']);
        }

        $user->app_no_work_experience = $firstJob ? 1 : 0;
        $user->save();

        return redirect()->route('employment.index')->with(
            'success',
            $firstJob ? 'Noted — this is your first job.' : 'Okay — add your previous jobs below.'
        );
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
