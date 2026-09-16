<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EligibilityController extends Controller
{
    public static function index()
    {
        $license = auth()->user()->eligibility;

        return view('pages.license', compact('license'));
    }

    public static function store(Request $request)
    {
        try {

            // A licence that is added must be complete. Some never expire (civil
            // service eligibility, for one), so the expiry date is required
            // unless the applicant says it does not expire.
            $validator = Validator::make($request->all(), [
                'license-id' => 'nullable|numeric',
                'license-registration-date' => 'required|date|before_or_equal:today',
                'license-no-expiry' => 'nullable|boolean',
                'license-valid-until' => 'nullable|required_unless:license-no-expiry,1|date',
                'license-type' => 'required|string|max:50',
                'license-profession' => 'required|string|max:50',
                'license-attachment' => 'nullable|file',
                'license-attachment-current' => 'nullable|string'
            ]);

            $validator->setAttributeNames([
                'license-registration-date' => 'Registration date',
                'license-valid-until' => 'Validity',
                'license-type' => 'Type',
                'license-profession' => 'Profession',
                'license-attachment' => 'Attachment',
                'license-attachment-current' => 'Current attachment'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            if ($request->hasFile('license-attachment')) {
                $file = $request->file('license-attachment');
                $fileName = time() . '_' . auth()->user()->app_id . '.' . $file->getClientOriginalExtension();

                $fileName = basename(FileService::reduceImageFileSizeToWebP(
                    $file->getRealPath(), 
                    'applicant/licenses/'.$fileName,
                    'public'
                ));

                $validated['license-attachment'] = $fileName;
            }

            $eligibility = auth()->user()->eligibility()->firstOrNew(['el_id' => $validated['license-id']]);

            $eligibility->el_type = $validated['license-type'];
            $eligibility->el_profession = $validated['license-profession'];
            $eligibility->el_regdate = $validated['license-registration-date'];
            $eligibility->el_expdate = !empty($validated['license-no-expiry']) ? null : $validated['license-valid-until'];
            $eligibility->el_file = !empty($validated['license-attachment']) ? $validated['license-attachment'] : ($validated['license-attachment-current'] ?? '');

            $eligibility->save();

            return redirect()->route('license.index')->with('success', 'License info updated');
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
            auth()->user()->eligibility()->find($id)->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }
}
