<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CharacterRefController extends Controller
{
    public static function index()
    {
        $characterref = auth()->user()->characterRef;

        return view('pages.characterref', compact('characterref'));
    }

    public static function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'characterref-id' => 'nullable|numeric',
                'characterref-name' => 'required|string',
                'characterref-position' => 'nullable|string',
                'characterref-company' => 'nullable|string',
                'characterref-address' => 'required|string',
                'characterref-contact' => 'required|string',
                'characterref-relationship' => 'required|string'
            ]);

            $validator->setAttributeNames([
                'characterref-name' => 'Name',
                'characterref-position' => 'Position',
                'characterref-company' => 'Company',
                'characterref-address' => 'Address',
                'characterref-contact' => 'Contact',
                'characterref-relationship' => 'Relationship'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            $characterRef = auth()->user()->characterRef()->firstOrNew(['ref_id' => $validated['characterref-id']]);
            
            $characterRef->ref_fullname = $validated['characterref-name'];
            $characterRef->ref_position = $validated['characterref-position'];
            $characterRef->ref_company = $validated['characterref-company'];
            $characterRef->ref_address = $validated['characterref-address'];
            $characterRef->ref_contact = $validated['characterref-contact'];
            $characterRef->ref_relationship = $validated['characterref-relationship'];
            $characterRef->ref_timestamp = now();

            $characterRef->save();

            return redirect()->route('characterref.index')->with('success', 'characterref info updated');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }

    public static function delete($id)
    {
        try {
            auth()->user()->characterRef()->find($id)->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }
}
