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

        // Pull the position from the applicant's most recent job application,
        // rather than the stale free-text snapshot in app_posapplied.
        $latestApplication = Application::where('app_id', $user?->app_id)
            ->orderByDesc('applied_at')
            ->first();

        $appliedPosition = $latestApplication?->jobPosting()?->posting_title;

        return view('pages.personal', compact('user', 'provinceList', 'municipalityList', 'barangayList', 'appliedPosition'));
    }

    public static function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'app-code' => 'required|string|max:255',
                'position-applied' => 'required|string|max:255',
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

                $user = new User();

                // app_code is this application's password column —
                // User::getAuthPassword() already points Laravel at it. Stored
                // hashed; AuthController verifies with Hash::check().
                $user->app_code = Hash::make($validated['app-code']);
                $user->app_posapplied = $validated['position-applied'];
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

                $user->app_posapplied = $validated['position-applied'];
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

    public static function storeProfileImg(Request $request)
    {
        try {
            $request->validate([
                // 'appid' => 'required|integer',
                'image' => 'mimes:jpg,jpeg,png'
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '_' . auth()->user()->app_id . '.' . $file->getClientOriginalExtension();

                $fileName = basename(FileService::reduceImageFileSizeToWebP(
                    $file->getRealPath(),
                    'applicant/images/' . $fileName,
                    'public'
                ));

                auth()->user()->update(['app_img' => $fileName]);

                return response()->json(['success' => true]);
            }

            return response()->json(['error' => 'No file uploaded'], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
