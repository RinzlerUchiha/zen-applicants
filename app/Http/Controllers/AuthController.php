<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JobApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Mirrors showRegisterForm(): arriving from a job posting keeps that
        // posting so the application can be submitted straight after login.
        if ($request->has('job')) {
            session(['intended_job_id' => $request->query('job')]);
        }

        return view('auth.login');
    }

    public function showRegisterForm(Request $request)
    {
        if ($request->has('job')) {
            session(['intended_job_id' => $request->query('job')]);
        }

        // The job posting is the source of truth for what the applicant is
        // applying to. Resolving it here means the form can show the position
        // rather than asking the applicant to type one, which could otherwise
        // disagree with the posting the application is actually attached to.
        $intendedJobId = session('intended_job_id');
        $posting = $intendedJobId
            ? DB::connection('zen')->table('tbl_job_posting')
                ->where('id', $intendedJobId)
                ->where('status', 'Published')
                ->first()
            : null;

        // A posting that has closed since they clicked through is no longer a
        // valid target — drop it rather than attach the application to it.
        if ($intendedJobId && !$posting) {
            session()->forget('intended_job_id');
        }

        $provinceList = DB::connection('zen')->table('tbl_province')->get();
        $municipalityList = DB::connection('zen')->table('tbl_municipality as a')
            ->leftJoin('tbl_province as b', 'pr_code', '=', 'ct_province')
            ->select('a.*', 'b.pr_name as ct_province_name')
            ->get();
        $barangayList = DB::connection('zen')->table('tbl_barangay as a')
            ->leftJoin('tbl_municipality as b', 'ct_id', '=', 'br_city')
            ->select('a.*', 'b.ct_name as br_city_name')
            ->get();
        return view('auth.register', compact('provinceList', 'municipalityList', 'barangayList', 'posting'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required_without:mobile',
            'mobile' => 'required_without:email',
            'code' => 'required|string',
        ], [
            'email.required_without' => 'Either Email or Mobile No. must be filled.',
            'mobile.required_without' => 'Either Email or Mobile No. must be filled.',
        ]);

        // if (Auth::attempt($credentials)) {
        //     $request->session()->regenerate();
        //     return redirect()->intended('/dashboard');
        // }

        // Identify by email or mobile only — the password is verified
        // separately below. validate() omits whichever field was left blank,
        // so both are read defensively and an absent one is not matched
        // against NULL.
        $email = $credentials['email'] ?? null;
        $mobile = $credentials['mobile'] ?? null;

        if ($email === null && $mobile === null) {
            return $this->failedLogin($request);
        }

        $user = User::where(function ($q) use ($email, $mobile) {
            if ($email !== null) {
                $q->orWhere('app_email', $email);
            }
            if ($mobile !== null) {
                $q->orWhere('app_mobile', $mobile);
            }
        })->first();

        if (!$user || !$this->passwordMatches($credentials['code'], $user)) {
            return $this->failedLogin($request);
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Came here from a job posting — submit that application now rather
        // than making the applicant find the posting again.
        $intendedJobId = session()->pull('intended_job_id');

        if ($intendedJobId) {
            $result = JobApplicationService::apply($user->app_id, $intendedJobId);

            return redirect()
                ->route('applications.index')
                ->with($result['success'] ? 'success' : 'error', $result['message']);
        }

        return redirect()->intended(route('personal.show'));
    }

    /**
     * Verifies the submitted password against app_code, this application's
     * password column (see User::getAuthPassword()).
     *
     * LEGACY COMPATIBILITY: app_code was previously stored in plain text.
     * Rows created before hashing was introduced are compared literally once,
     * then immediately re-saved as a hash, so those accounts migrate
     * themselves on first login and the applicant notices nothing. Once no
     * plain-text rows remain, the legacy branch can be deleted.
     */
    private function passwordMatches(string $submitted, User $user): bool
    {
        $stored = (string) $user->app_code;

        if ($stored === '') {
            return false;
        }

        // password_get_info() reports algo 0 / null for anything that is not
        // a PHP password hash, which is how a legacy plain-text value is told
        // apart from a hashed one.
        $info = password_get_info($stored);
        $isHashed = !empty($info['algo']);

        if ($isHashed) {
            return Hash::check($submitted, $stored);
        }

        if (!hash_equals($stored, $submitted)) {
            return false;
        }

        $user->app_code = Hash::make($submitted);
        $user->save();

        return true;
    }

    private function failedLogin(Request $request)
    {
        return back()
            ->withErrors(['error' => 'Invalid credentials'])
            ->withInput($request->only(['email', 'mobile']));
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User|null $user */
        // $user = Auth::user();

        // Optional: Clear custom remember token from database
        // if ($user) {
        //     $user->remember_token = null;
        //     $user->save();
        // }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withHeaders(['Clear-Site-Data' => '"cache", "storage", "executionContexts", "prefetchCache", "prerenderCache"']);
    }
}
