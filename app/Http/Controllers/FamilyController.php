<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FamilyController extends Controller
{
    public static function index()
    {
        $family = auth()->user()->family;

        return view('pages.family', compact('family'));
    }

    public static function store(Request $request)
    {
        try {

            // The family section may have no records, but a family member who is
            // added must be complete. Limits match the column widths, so an
            // over-long value is a clear message rather than a database error.
            $validator = Validator::make($request->all(), [
                'family-id' => 'nullable|numeric',
                'family-relationship' => 'required|string|max:20',
                'family-firstname' => 'required|string|max:20',
                'family-middlename' => 'nullable|string|max:20',
                'family-lastname' => 'required|string|max:20',
                'family-suffix' => 'nullable|string|max:10',
                'family-maidenname' => 'nullable|string|max:20',
                'family-birthdate' => 'required|date|before:today',
                'family-sex' => 'required|string|max:10',
                'family-contact' => 'required|string|max:20',
                'family-address' => 'required|string',
                'family-occupation' => 'nullable|string|max:20',
                'family-workplace' => 'nullable|string'
            ]);

            $validator->setAttributeNames([
                'family-relationship' => 'Relationship',
                'family-firstname' => 'First name',
                'family-middlename' => 'Middle name',
                'family-lastname' => 'Last name',
                'family-suffix' => 'Suffix',
                'family-maidenname' => 'Maiden name',
                'family-birthdate' => 'Birth date',
                'family-sex' => 'Sex',
                'family-contact' => 'Contact',
                'family-address' => 'Address',
                'family-occupation' => 'Occupation',
                'family-workplace' => 'Workplace',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            $family = auth()->user()->family()->firstOrNew(['fam_id' => $validated['family-id']]);

            $family->fam_relationship = $validated['family-relationship'];
            $family->fam_firstname = $validated['family-firstname'];
            $family->fam_midname = $validated['family-middlename'];
            $family->fam_lastname = $validated['family-lastname'];
            $family->fam_suffix = $validated['family-suffix'];
            $family->fam_maidenname = $validated['family-maidenname'];
            $family->fam_birthdate = $validated['family-birthdate'];
            $family->fam_sex = $validated['family-sex'];
            $family->fam_contact = $validated['family-contact'];
            $family->fam_add = $validated['family-address'];
            $family->fam_occupation = $validated['family-occupation'];
            $family->fam_workplace = $validated['family-workplace'];
            $family->status = 1;

            $family->save();

            return redirect()->route('family.index')->with('success', 'Family info updated');
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
            auth()->user()->family()->find($id)->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }
}
