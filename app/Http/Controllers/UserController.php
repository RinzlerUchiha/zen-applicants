<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\User;
use App\Services\FileService;
use App\Services\JobApplicationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public static function show()
    {
        $user = auth()->user();
        $provinceList = DB::connection('zen')->table('tbl_province')->get();
        $municipalityList = DB::connection('zen')->table('tbl_municipality as a')
            ->leftJoin('tbl_province as b', 'pr_code', '=', 'ct_province')
            ->select('a.*', 'b.pr_name as ct_province_name')
            ->get();
        $barangayList = DB::connection('zen')->table('tbl_barangay as a')
            ->leftJoin('tbl_municipality as b', 'ct_id', '=', 'br_city')
            ->select('a.*', 'b.ct_name as br_city_name')
            ->get();

        // Every position applied for, from the applications themselves
        // (tblapp_applications.job_posting_id → tbl_job_posting). An applicant
        // can apply to several postings, so this is a list — newest first — not
        // one value. Titles are looked up in a single query.
        $applications = Application::where('app_id', $user?->app_id)
            ->orderByDesc('applied_at')
            ->get(['id', 'job_posting_id', 'status', 'applied_at']);

        $titles = $applications->pluck('job_posting_id')->filter()->isEmpty()
            ? collect()
            : DB::connection('zen')->table('tbl_job_posting')
                ->whereIn('id', $applications->pluck('job_posting_id')->filter()->unique())
                ->pluck('posting_title', 'id');

        $appliedPositions = $applications
            ->map(fn ($application) => [
                'title' => $titles[$application->job_posting_id] ?? null,
                'status' => $application->status,
                'applied_at' => $application->applied_at,
            ])
            ->filter(fn ($application) => $application['title'])
            ->values();

        return view('pages.personal', compact('user', 'provinceList', 'municipalityList', 'barangayList', 'appliedPositions'));
    }

    public static function store(Request $request)
    {
        try {

            // This action serves two forms: registration (guest) and profile
            // editing (signed in). The password and the acknowledgement belong
            // to registration only — the profile form sends neither, so
            // applying those rules to it would make every profile edit fail.
            $isRegistration = !auth()->check();

            $rules = [
                'position-applied' => 'nullable|string|max:255',
                'personal-firstname' => 'required|string|max:255',
                'personal-middlename' => 'nullable|string|max:255', // Middlename can be optional
                'personal-lastname' => 'required|string|max:255',
                'personal-suffix' => 'nullable|string|max:20', // Suffix is optional, e.g. Jr., Sr.

                'personal-email' => 'required|email|max:255', // Email should be unique
                'personal-contact' => 'required|string|max:13', // Contact +639, 09
                'personal-telephone' => 'nullable|string|max:20', // Telephone is optional, range for length

                'personal-padd-province' => 'nullable|string|max:255',
                'personal-padd-city' => 'nullable|string|max:255',
                'personal-padd-barangay' => 'nullable|string|max:255',
                'personal-padd-specific' => 'nullable|string|max:255', // Specific address details, e.g., street
                'personal-cadd-province' => 'nullable|string|max:255',
                'personal-cadd-city' => 'nullable|string|max:255',
                'personal-cadd-barangay' => 'nullable|string|max:255',
                'personal-cadd-specific' => 'nullable|string|max:255', // Corresponding for current address if available
                'personal-badd-province' => 'nullable|string|max:255',
                'personal-badd-city' => 'nullable|string|max:255',
                'personal-badd-barangay' => 'nullable|string|max:255',
                'personal-badd-specific' => 'nullable|string|max:255', // Birth address (optional)
                'personal-birthdate' => 'required|date|before:today', // Birthdate must be a date and in the past
                // 'personal-age' => 'required|numeric', // Employee age
                'personal-civil-status' => 'required|in:Single,Married,Separated/Divorced,Widow/Widower', // Example statuses
                'personal-sex' => 'required|in:Male,Female', // Gender
                'personal-bloodtype' => 'nullable|string|max:3', // Blood type should be a 3-character string
                'personal-height' => 'nullable|numeric', // |min:50|max:250 Height in centimeters (realistic range)
                'personal-weight' => 'nullable|numeric', // |min:20|max:300 Weight in kilograms (realistic range)
                'personal-nationality' => 'nullable|string|max:255',
                'personal-religion' => 'nullable|string|max:255',
                'personal-dialect' => 'nullable|string|max:255',
                'personal-sss' => 'nullable|string|max:50', // SSS number format (if applicable)
                'personal-hdmf' => 'nullable|string|max:50', // HDMF (Pag-IBIG) number
                'personal-phic' => 'nullable|string|max:50', // PHIC (PhilHealth) number
                'personal-tin' => 'nullable|string|max:50', // TIN (Tax Identification Number)
            ];

            if ($isRegistration) {
                // Acknowledgement of the Terms of Use and the Privacy Notice is
                // captured at the point of collection, as RA 10173 requires the
                // data subject to be informed before their information is
                // processed. The checkbox sits immediately above the submit
                // button; this rule is what actually enforces it.
                $rules['privacy-acknowledged'] = 'accepted';
                // min:8 matches the rule the form has always shown the
                // applicant. It was previously only enforced in the browser.
                $rules['app-code'] = 'required|string|min:8|max:255';
            }

            $validator = Validator::make($request->all(), $rules);

            $validator->setCustomMessages([
                'privacy-acknowledged.accepted' => 'Please tick the box to confirm you have read and agree to the Terms of Use and the Data Privacy Notice.',
                'app-code.required' => 'Please choose a password.',
                'app-code.min' => 'Your password must be at least 8 characters.',
            ]);

            $validator->setAttributeNames([
                'position-applied' => 'Position',
                'personal-firstname' => 'Firstname',
                'personal-lastname' => 'Lastname',
                'personal-email' => 'Email',
                'personal-contact' => 'Contact',
                'personal-birthdate' => 'Birth date',
                'personal-civil-status' => 'Civil status',
                'personal-sex' => 'Sex',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            $isNew = false;

            $user = auth()->user();

            if (!$user) {
                $isNew = true;

                // $existingUser = User::where('email', 'johndoe@example.com')->first();

                // $user = new User();
                // $user->name = 'John Doe';
                // $user->email = 'johndoe@example.com';
                // $user->password = bcrypt('secretpassword');
                // $user->save();

                // $user = User::create([
                //     // 'app_code' => Str::random(10),
                //     'app_code' => $validated['app-code'],
                //     'app_posapplied' => $validated['position-applied'],
                //     'app_date' => now()->format('Y-m-d'),
                //     'app_lname' => $validated['personal-lastname'],
                //     'app_fname' => $validated['personal-firstname'],
                //     'app_mname' => $validated['personal-middlename'],
                //     'app_suffix' => $validated['personal-suffix'],
                //     'app_mobile' => $validated['personal-contact'],
                //     'app_telephone' => $validated['personal-telephone'],
                //     'app_email' => $validated['personal-email'],
                //     'app_cstatus' => $validated['personal-civil-status'],
                //     'app_sex' => $validated['personal-sex'],
                //     'app_dialect' => $validated['personal-dialect'],
                //     'app_religion' => $validated['personal-religion'],
                //     'app_nationality' => $validated['personal-nationality'],
                //     'app_btype' => $validated['personal-bloodtype'],
                //     'app_tin' => $validated['personal-tin'],
                //     'app_philhealth' => $validated['personal-phic'],
                //     'app_sss' => $validated['personal-sss'],
                //     'app_pagibig' => $validated['personal-hdmf'],
                //     'app_bdate' => $validated['personal-birthdate'],
                //     'app_age' => Carbon::parse($validated['personal-birthdate'])->age,
                //     // 'app_bplace' => $validated['data'],
                //     // 'app_caddress' => $validated['data'],
                //     // 'app_paddress' => $validated['data'],
                //     'app_height' => $validated['personal-height'],
                //     'app_weight' => $validated['personal-weight'],
                //     // 'app_img' => $validated['data'],
                //     // 'app_code' => $validated['data'],
                //     // 'app_timestamp' => $validated['data'],
                //     // 'app_status' => $validated['data'],
                //     // 'app_hiredt' => $validated['data'],
                // ]);

                // Resolved from the posting the applicant clicked through from,
                // re-checked as still Published at this exact moment.
                $intendedPosting = session('intended_job_id')
                    ? DB::connection('zen')->table('tbl_job_posting')
                        ->where('id', session('intended_job_id'))
                        ->where('status', 'Published')
                        ->first()
                    : null;

                $user = new User();

                // app_code is this application's password column —
                // User::getAuthPassword() already points Laravel at it. Stored
                // hashed; AuthController verifies with Hash::check().
                $user->app_code = Hash::make($validated['app-code']);
                // The job posting is the sole source of truth for the position.
                // The sign-up form neither asks for it nor submits it, so there
                // is no applicant-supplied value to fall back to: creating a
                // profile without choosing a posting leaves this empty until
                // they apply to one.
                //
                // Empty string, not null — the column is NOT NULL, and this is
                // the value the form's hidden field used to submit in the same
                // situation. Changing the column is a schema decision, not part
                // of this UX correction.
                $user->app_posapplied = $intendedPosting->posting_title ?? '';
                $user->app_date = now()->format('Y-m-d');
                $user->app_lname = $validated['personal-lastname'];
                $user->app_fname = $validated['personal-firstname'];
                $user->app_mname = $validated['personal-middlename'];
                $user->app_suffix = $validated['personal-suffix'];
                $user->app_mobile = $validated['personal-contact'];
                $user->app_telephone = $validated['personal-telephone'];
                $user->app_email = $validated['personal-email'];
                $user->app_cstatus = $validated['personal-civil-status'];
                $user->app_sex = $validated['personal-sex'];
                $user->app_dialect = $validated['personal-dialect'];
                $user->app_religion = $validated['personal-religion'];
                $user->app_nationality = $validated['personal-nationality'];
                $user->app_btype = $validated['personal-bloodtype'];
                $user->app_tin = $validated['personal-tin'];
                $user->app_philhealth = $validated['personal-phic'];
                $user->app_sss = $validated['personal-sss'];
                $user->app_pagibig = $validated['personal-hdmf'];
                $user->app_bdate = $validated['personal-birthdate'];
                $user->app_age = Carbon::parse($validated['personal-birthdate'])->age;
                $user->app_height = $validated['personal-height'];
                $user->app_weight = $validated['personal-weight'];

                // When they were informed. Stored because an acknowledgement
                // that is not recorded cannot later be evidenced.
                $user->app_privacy_ack_at = now();

                $user->save();
            } else {
                // $user->update([
                //     'app_posapplied' => $validated['position-applied'],
                //     'app_lname' => $validated['personal-lastname'],
                //     'app_fname' => $validated['personal-firstname'],
                //     'app_mname' => $validated['personal-middlename'],
                //     'app_suffix' => $validated['personal-suffix'],
                //     'app_mobile' => $validated['personal-contact'],
                //     'app_telephone' => $validated['personal-telephone'],
                //     'app_email' => $validated['personal-email'],
                //     'app_cstatus' => $validated['personal-civil-status'],
                //     'app_sex' => $validated['personal-sex'],
                //     'app_dialect' => $validated['personal-dialect'],
                //     'app_religion' => $validated['personal-religion'],
                //     'app_nationality' => $validated['personal-nationality'],
                //     'app_btype' => $validated['personal-bloodtype'],
                //     'app_tin' => $validated['personal-tin'],
                //     'app_philhealth' => $validated['personal-phic'],
                //     'app_sss' => $validated['personal-sss'],
                //     'app_pagibig' => $validated['personal-hdmf'],
                //     'app_bdate' => $validated['personal-birthdate'],
                //     'app_age' => Carbon::parse($validated['personal-birthdate'])->age,
                //     'app_height' => $validated['personal-height'],
                //     'app_weight' => $validated['personal-weight'],
                // ]);

                // Editing the profile never touches app_posapplied. The positions an
                // applicant applied for belong to their applications, and this form
                // no longer sends one.

                $user->app_lname = $validated['personal-lastname'];
                $user->app_fname = $validated['personal-firstname'];
                $user->app_mname = $validated['personal-middlename'];
                $user->app_suffix = $validated['personal-suffix'];
                $user->app_mobile = $validated['personal-contact'];
                $user->app_telephone = $validated['personal-telephone'];
                $user->app_email = $validated['personal-email'];
                $user->app_cstatus = $validated['personal-civil-status'];
                $user->app_sex = $validated['personal-sex'];
                $user->app_dialect = $validated['personal-dialect'];
                $user->app_religion = $validated['personal-religion'];
                $user->app_nationality = $validated['personal-nationality'];
                $user->app_btype = $validated['personal-bloodtype'];
                $user->app_tin = $validated['personal-tin'];
                $user->app_philhealth = $validated['personal-phic'];
                $user->app_sss = $validated['personal-sss'];
                $user->app_pagibig = $validated['personal-hdmf'];
                $user->app_bdate = $validated['personal-birthdate'];
                $user->app_age = Carbon::parse($validated['personal-birthdate'])->age;
                $user->app_height = $validated['personal-height'];
                $user->app_weight = $validated['personal-weight'];

                $user->save();
            }

            $address = $user->address()->firstOrNew([]);

            $address->add_perm_prov = $validated['personal-padd-province'];
            $address->add_perm_city = $validated['personal-padd-city'];
            $address->add_perm_brngy = $validated['personal-padd-barangay'];
            $address->add_cur_prov = $validated['personal-cadd-province'];
            $address->add_cur_city = $validated['personal-cadd-city'];
            $address->add_cur_brngy = $validated['personal-cadd-barangay'];
            $address->add_birth_prov = $validated['personal-badd-province'];
            $address->add_birth_city = $validated['personal-badd-city'];
            $address->add_birth_brngy = $validated['personal-badd-barangay'];
            $address->add_perm_location = $validated['personal-padd-specific'];
            $address->add_cur_location = $validated['personal-cadd-specific'];
            $address->add_birth_location = $validated['personal-badd-specific'];
            $address->add_status = 1;

            $address->save();

            if ($isNew) {
                Auth::login($user);
                $request->session()->regenerate();

                $intendedJobId = session()->pull('intended_job_id');

                if ($intendedJobId) {
                    $result = JobApplicationService::apply($user->app_id, $intendedJobId);
                    return redirect()
                        ->route('applications.index')
                        ->with($result['success'] ? 'success' : 'error', $result['message']);
                }

                return redirect()->route('careers.index')->with('success', 'Account created! Browse open positions and apply below.');
            }

            return redirect()->route('personal.show')->with('success', ($isNew ? 'Profile created!' : 'Personal info updated!'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to process information: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Saves the applicant's profile photo on its own, independent of Edit.
     *
     * Always answers in JSON with success and either the photo's URL or a
     * message the page can show. Previously a failure still returned 200 and the
     * page reloaded regardless, so a photo that did not save looked as if it had.
     */
    public static function storeProfileImg(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ], [
            'image.required' => 'Please choose a photo.',
            'image.mimes' => 'Please use a JPG or PNG photo.',
            'image.max' => 'That photo is too large. The limit is 5 MB.',
            'image.file' => 'That photo could not be read. Please try another file.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first('image')], 422);
        }

        $user = auth()->user();
        $file = $request->file('image');
        $folder = 'applicant/images';

        // app_img is varchar(20): a base-36 timestamp keeps the name short enough
        // for any realistic applicant id. The extension comes from the file's
        // content, never from the name it was uploaded with.
        $base = base_convert((string) time(), 10, 36) . '_' . $user->app_id;
        $extension = $file->extension() === 'png' ? 'png' : 'jpg';

        try {
            $stored = basename(FileService::reduceImageFileSizeToWebP(
                $file->getRealPath(),
                "$folder/$base.$extension",
                'public'
            ));
        } catch (\Throwable $e) {
            // Compression needs PHP's GD extension. Without it the photo is still
            // worth keeping — store the original rather than lose it.
            report($e);

            try {
                $stored = "$base.$extension";
                $file->storeAs($folder, $stored, 'public');
            } catch (\Throwable $e) {
                report($e);

                return response()->json(['success' => false, 'error' => 'That photo could not be saved. Please try again.'], 500);
            }
        }

        $user->app_img = $stored;
        $user->save();

        return response()->json(['success' => true, 'url' => url('/file/app-img/' . $stored)]);
    }
}
