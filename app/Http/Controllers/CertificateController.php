<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CertificateController extends Controller
{
    public static function index()
    {
        $certificate = auth()->user()->certificate;

        return view('pages.certificate', compact('certificate'));
    }

    public static function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'certificate-id' => 'nullable|numeric',
                'certificate-title' => 'required|string|max:50',
                'certificate-location' => 'required|string|max:100',
                'certificate-completion-date' => 'required|date|before_or_equal:today',
                'certificate-speaker' => 'nullable|string|max:100',
                'certificate-attachment' => 'nullable|file',
                'certificate-attachment-current' => 'nullable|string'
            ]);

            $validator->setAttributeNames([
                'certificate-title' => 'Title',
                'certificate-location' => 'Location',
                'certificate-completion-date' => 'Completion Date',
                'certificate-speaker' => 'Speaker',
                'certificate-attachment' => 'Attachment',
                'certificate-attachment-current' => 'Current attachment'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            if ($request->hasFile('certificate-attachment')) {
                $file = $request->file('certificate-attachment');
                $fileName = time() . '_' . auth()->user()->app_id . '.' . $file->getClientOriginalExtension();

                $fileName = basename(FileService::reduceImageFileSizeToWebP(
                    $file->getRealPath(), 
                    'applicant/certificates/'.$fileName,
                    'public'
                ));

                $validated['certificate-attachment'] = $fileName;
            }

            $certificate = auth()->user()->certificate()->firstOrNew(['cert_id' => $validated['certificate-id']]);

            $certificate->cert_title = $validated['certificate-title'];
            $certificate->cert_address = $validated['certificate-location'];
            $certificate->cert_date = $validated['certificate-completion-date'];
            $certificate->cert_speaker = $validated['certificate-speaker'];
            $certificate->cert_file = !empty($validated['certificate-attachment']) ? $validated['certificate-attachment'] : ($validated['certificate-attachment-current'] ?? '');
            
            $certificate->save();

            return redirect()->route('certificate.index')->with('success', 'Certificate info updated');
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
            auth()->user()->certificate()->find($id)->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to process information: ' . $e->getMessage()]);
        }
    }
}
