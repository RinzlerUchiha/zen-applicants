<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        $provinceList = DB::connection('zen')->table('tbl_province')->get();
        $municipalityList = DB::connection('zen')->table('tbl_municipality as a')
            ->leftJoin('tbl_province as b', 'pr_code', '=', 'ct_province')
            ->select('a.*', 'b.pr_name as ct_province_name')
            ->get();
        $barangayList = DB::connection('zen')->table('tbl_barangay as a')
            ->leftJoin('tbl_municipality as b', 'ct_id', '=', 'br_city')
            ->select('a.*', 'b.ct_name as br_city_name')
            ->get();
        return view('auth.register', compact('provinceList', 'municipalityList', 'barangayList'));
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

        $user = User::where('app_code', $credentials['code'])
        ->where(function($q) use($credentials) {
            $q->where('app_email', $credentials['email'])
            ->orWhere('app_mobile', $credentials['mobile']);
        })
        ->first();

        if($user /* && Hash::check($request->password, $user->U_Password) */)
        {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended(route('personal.show'));
        }

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
