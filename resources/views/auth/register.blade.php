@extends('layouts.app')

@section('title', 'Create your applicant profile')

@php
    /*
        One form, presented a step at a time.

        The fields, their names, and the single POST to register.store are
        unchanged — stepping is presentation only. Every step stays in the DOM
        (hidden, not detached), so the browser submits the whole form exactly
        as it did when this was one long page.

        `fields` is used to bounce the applicant to the first step that has a
        validation error, rather than dropping them on step 1 with a list of
        problems they cannot see.
    */
    $steps = [
        ['key' => 'name',    'label' => 'Name',      'title' => 'Your name',
         'fields' => ['personal-firstname', 'personal-middlename', 'personal-lastname', 'personal-suffix']],

        ['key' => 'contact', 'label' => 'Contact',   'title' => 'How we reach you',
         'fields' => ['personal-email', 'personal-contact', 'personal-telephone']],

        ['key' => 'address', 'label' => 'Address',   'title' => 'Where you live',
         'fields' => ['personal-padd-province', 'personal-padd-city', 'personal-padd-barangay', 'personal-padd-specific',
                      'personal-cadd-province', 'personal-cadd-city', 'personal-cadd-barangay', 'personal-cadd-specific']],

        ['key' => 'about',   'label' => 'About you', 'title' => 'About you',
         'fields' => ['personal-birthdate', 'personal-civil-status', 'personal-sex', 'personal-nationality',
                      'personal-badd-province', 'personal-badd-city', 'personal-badd-barangay', 'personal-badd-specific']],

        ['key' => 'extra',   'label' => 'Optional',  'title' => 'A few optional details',
         'fields' => ['personal-bloodtype', 'personal-height', 'personal-weight', 'personal-religion', 'personal-dialect',
                      'personal-sss', 'personal-hdmf', 'personal-phic', 'personal-tin']],

        ['key' => 'finish',  'label' => 'Finish',    'title' => 'Set a password and confirm',
         'fields' => ['app-code', 'privacy-acknowledged']],
    ];

    $total = count($steps);
@endphp

@section('body')
<main class="zn-canvas">
    <div class="zn-narrow">

        {{-- The position is context, not a question. It comes from the posting
             the applicant clicked through from and is shown here so they can
             see what they are applying to — there is deliberately no field,
             no input and no way to change it from this form. --}}
        @if ($posting)
            <div class="zn-applyfor">
                <span class="zn-applyfor-ico"><i class="bi bi-briefcase-fill"></i></span>
                <div>
                    <b>You're applying for {{ $posting->posting_title }}</b>
                    <span>
                        Your application will be linked to this posting.
                        <a class="zn-link" href="{{ route('careers.show', $posting->id) }}"
                           target="_blank" rel="noopener">View posting</a>
                    </span>
                </div>
            </div>
        @else
            <div class="zn-applyfor muted">
                <span class="zn-applyfor-ico"><i class="bi bi-person-plus-fill"></i></span>
                <div>
                    <b>You're creating a profile</b>
                    <span>
                        You haven't picked a position yet — that's fine. You can apply to any
                        position once this is done.
                        <a class="zn-link" href="{{ route('careers.index') }}">Browse open positions</a>
                    </span>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="zn-toast"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="zn-toast error" style="display:block">
                <b><i class="bi bi-exclamation-circle-fill"></i> Please check the highlighted fields</b>
                <ul style="font-weight:400;margin-top:5px">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- ============ Step chrome ============ --}}
        <div class="zn-formtop" id="wizard-top">
            <div class="zn-formtop-head">
                <div>
                    <p class="zn-crumb">Step <span id="step-now">1</span> of {{ $total }}</p>
                    <p class="zn-page-title" style="margin-bottom:3px" id="step-title">{{ $steps[0]['title'] }}</p>
                    <p class="zn-page-sub" style="margin-bottom:0">
                        A few short steps. You can go back and change anything before you finish.
                    </p>
                </div>
            </div>

            <div class="zn-stepper" role="tablist" aria-label="Sign-up steps">
                @foreach ($steps as $i => $step)
                    <button type="button" class="zn-stepper-item {{ $i === 0 ? 'active' : '' }}"
                            data-goto="{{ $i }}" role="tab"
                            aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                        <span class="zn-stepper-mark">{{ $i + 1 }}</span>
                        <span class="zn-stepper-label">{{ $step['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="zn-bar zn-wizard-bar"><i id="step-bar" style="width: {{ round(100 / $total) }}%"></i></div>

        <form id="form-personal" action="{{ route('register.store') }}" method="POST" novalidate>
            @csrf

            {{-- ============ 1 · Name ============ --}}
            <section class="zn-card zn-formcard zn-wstep" data-step="0">
                <div class="zn-section"><h5>Your name</h5></div>
                <p class="zn-help">Enter it as it appears on your government IDs.</p>

                <div class="zn-grid">
                    <div class="zn-fld zn-col-4">
                        <label for="personal-firstname">First name <span class="zn-req">*</span></label>
                        <input type="text" name="personal-firstname" id="personal-firstname"
                               value="{{ old('personal-firstname') }}">
                    </div>
                    <div class="zn-fld zn-col-4">
                        <label for="personal-middlename">Middle name <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-middlename" id="personal-middlename"
                               value="{{ old('personal-middlename') }}">
                    </div>
                    <div class="zn-fld zn-col-4">
                        <label for="personal-lastname">Last name <span class="zn-req">*</span></label>
                        <input type="text" name="personal-lastname" id="personal-lastname"
                               value="{{ old('personal-lastname') }}">
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-suffix">Suffix <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-suffix" id="personal-suffix"
                               value="{{ old('personal-suffix') }}" placeholder="Jr., Sr., III">
                    </div>
                </div>
            </section>

            {{-- ============ 2 · Contact ============ --}}
            <section class="zn-card zn-formcard zn-wstep" data-step="1" hidden>
                <div class="zn-section"><h5>How we reach you</h5></div>
                <p class="zn-help">
                    We'll use these to tell you about your application — and you'll sign in with
                    your email or mobile number.
                </p>

                <div class="zn-grid">
                    <div class="zn-fld zn-col-5">
                        <label for="personal-email">Email <span class="zn-req">*</span></label>
                        <input type="email" name="personal-email" id="personal-email"
                               value="{{ old('personal-email') }}" placeholder="you@example.com"
                               autocomplete="email">
                    </div>
                    <div class="zn-fld zn-col-4">
                        <label for="personal-contact">Mobile number <span class="zn-req">*</span></label>
                        <input type="text" name="personal-contact" id="personal-contact"
                               value="{{ old('personal-contact') }}" placeholder="09#########"
                               autocomplete="tel">
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-telephone">Telephone <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-telephone" id="personal-telephone"
                               value="{{ old('personal-telephone') }}">
                    </div>
                </div>
            </section>

            {{-- ============ 3 · Address ============ --}}
            <section class="zn-card zn-formcard zn-wstep" data-step="2" hidden>
                <div class="zn-section"><h5>Permanent address</h5></div>

                <div class="zn-grid">
                    <div class="zn-fld zn-col-3">
                        <label for="personal-padd-province">Province <span class="zn-req">*</span></label>
                        <select class="select-province" name="personal-padd-province" id="personal-padd-province">
                            <option value="">Select province</option>
                            @foreach ($provinceList as $list)
                                <option value="{{ $list->pr_name }}" @selected(old('personal-padd-province') === $list->pr_name)>{{ $list->pr_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-padd-city">City / Municipality <span class="zn-req">*</span></label>
                        <select class="select-city" name="personal-padd-city" id="personal-padd-city">
                            <option value="">Select city</option>
                            @foreach ($municipalityList as $list)
                                <option style="display:none" province="{{ $list->ct_province_name }}"
                                        value="{{ $list->ct_name }}" @selected(old('personal-padd-city') === $list->ct_name)>{{ $list->ct_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-padd-barangay">Barangay <span class="zn-req">*</span></label>
                        <select class="select-barangay" name="personal-padd-barangay" id="personal-padd-barangay">
                            <option value="">Select barangay</option>
                            @foreach ($barangayList as $list)
                                <option style="display:none" city="{{ $list->br_city_name }}"
                                        value="{{ $list->br_name }}" @selected(old('personal-padd-barangay') === $list->br_name)>{{ $list->br_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-padd-specific">Street / House no. <span class="zn-req">*</span></label>
                        <input type="text" name="personal-padd-specific" id="personal-padd-specific"
                               value="{{ old('personal-padd-specific') }}">
                    </div>
                </div>

                <div class="zn-formcard-head" style="margin-top:22px">
                    <div class="zn-section mb-0"><h5>Current address</h5></div>
                    {{-- Most applicants live where they're registered. One tick
                         beats retyping four fields. --}}
                    <label class="zn-check">
                        <input type="checkbox" id="same-as-permanent">
                        <span>Same as permanent address</span>
                    </label>
                </div>

                <div class="zn-grid" id="current-address-fields">
                    <div class="zn-fld zn-col-3">
                        <label for="personal-cadd-province">Province <span class="zn-req">*</span></label>
                        <select class="select-province" name="personal-cadd-province" id="personal-cadd-province">
                            <option value="">Select province</option>
                            @foreach ($provinceList as $list)
                                <option value="{{ $list->pr_name }}" @selected(old('personal-cadd-province') === $list->pr_name)>{{ $list->pr_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-cadd-city">City / Municipality <span class="zn-req">*</span></label>
                        <select class="select-city" name="personal-cadd-city" id="personal-cadd-city">
                            <option value="">Select city</option>
                            @foreach ($municipalityList as $list)
                                <option style="display:none" province="{{ $list->ct_province_name }}"
                                        value="{{ $list->ct_name }}" @selected(old('personal-cadd-city') === $list->ct_name)>{{ $list->ct_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-cadd-barangay">Barangay <span class="zn-req">*</span></label>
                        <select class="select-barangay" name="personal-cadd-barangay" id="personal-cadd-barangay">
                            <option value="">Select barangay</option>
                            @foreach ($barangayList as $list)
                                <option style="display:none" city="{{ $list->br_city_name }}"
                                        value="{{ $list->br_name }}" @selected(old('personal-cadd-barangay') === $list->br_name)>{{ $list->br_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-cadd-specific">Street / House no. <span class="zn-req">*</span></label>
                        <input type="text" name="personal-cadd-specific" id="personal-cadd-specific"
                               value="{{ old('personal-cadd-specific') }}">
                    </div>
                </div>
            </section>

            {{-- ============ 4 · About you ============ --}}
            <section class="zn-card zn-formcard zn-wstep" data-step="3" hidden>
                <div class="zn-section"><h5>About you</h5></div>

                <div class="zn-grid">
                    <div class="zn-fld zn-col-3">
                        <label for="personal-birthdate">Birth date <span class="zn-req">*</span></label>
                        <input type="date" name="personal-birthdate" id="personal-birthdate"
                               value="{{ old('personal-birthdate') }}">
                    </div>
                    <div class="zn-fld zn-col-2">
                        <label for="personal-age">Age</label>
                        {{-- Read-only: derived from birth date, not asked for. --}}
                        <input type="text" id="personal-age" readonly tabindex="-1" placeholder="—">
                    </div>
                    <div class="zn-fld zn-col-4">
                        <label for="personal-civil-status">Civil status <span class="zn-req">*</span></label>
                        <select name="personal-civil-status" id="personal-civil-status">
                            <option value="">Select</option>
                            @foreach (['Single', 'Married', 'Separated/Divorced', 'Widow/Widower'] as $option)
                                <option value="{{ $option }}" @selected(old('personal-civil-status') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-sex">Sex <span class="zn-req">*</span></label>
                        <select name="personal-sex" id="personal-sex">
                            <option value="">Select</option>
                            @foreach (['Male', 'Female'] as $option)
                                <option value="{{ $option }}" @selected(old('personal-sex') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-4">
                        <label for="personal-nationality">Nationality <span class="zn-req">*</span></label>
                        <input type="text" name="personal-nationality" id="personal-nationality"
                               value="{{ old('personal-nationality') }}" placeholder="Filipino">
                    </div>
                </div>

                <div class="zn-section mt-4"><h5>Place of birth</h5></div>
                <p class="zn-help">Optional — you can fill this in later from your profile.</p>

                <div class="zn-grid">
                    <div class="zn-fld zn-col-3">
                        <label for="personal-badd-province">Province <span class="zn-opt">optional</span></label>
                        <select class="select-province" name="personal-badd-province" id="personal-badd-province">
                            <option value="">Select province</option>
                            @foreach ($provinceList as $list)
                                <option value="{{ $list->pr_name }}" @selected(old('personal-badd-province') === $list->pr_name)>{{ $list->pr_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-badd-city">City / Municipality <span class="zn-opt">optional</span></label>
                        <select class="select-city" name="personal-badd-city" id="personal-badd-city">
                            <option value="">Select city</option>
                            @foreach ($municipalityList as $list)
                                <option style="display:none" province="{{ $list->ct_province_name }}"
                                        value="{{ $list->ct_name }}" @selected(old('personal-badd-city') === $list->ct_name)>{{ $list->ct_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-badd-barangay">Barangay <span class="zn-opt">optional</span></label>
                        <select class="select-barangay" name="personal-badd-barangay" id="personal-badd-barangay">
                            <option value="">Select barangay</option>
                            @foreach ($barangayList as $list)
                                <option style="display:none" city="{{ $list->br_city_name }}"
                                        value="{{ $list->br_name }}" @selected(old('personal-badd-barangay') === $list->br_name)>{{ $list->br_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-badd-specific">Street / House no. <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-badd-specific" id="personal-badd-specific"
                               value="{{ old('personal-badd-specific') }}">
                    </div>
                </div>
            </section>

            {{-- ============ 5 · Optional details ============ --}}
            <section class="zn-card zn-formcard zn-wstep" data-step="4" hidden>
                <div class="zn-section"><h5>A few optional details</h5></div>
                <p class="zn-help">
                    Nothing on this step is required. Skip it and add anything you want later from
                    your profile.
                </p>

                <div class="zn-grid">
                    <div class="zn-fld zn-col-3">
                        <label for="personal-bloodtype">Blood type <span class="zn-opt">optional</span></label>
                        <select name="personal-bloodtype" id="personal-bloodtype">
                            <option value="">Select</option>
                            @foreach (['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $option)
                                <option value="{{ $option }}" @selected(old('personal-bloodtype') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-height">Height (cm) <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-height" id="personal-height"
                               value="{{ old('personal-height') }}">
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-weight">Weight (kg) <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-weight" id="personal-weight"
                               value="{{ old('personal-weight') }}">
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-religion">Religion <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-religion" id="personal-religion"
                               value="{{ old('personal-religion') }}">
                    </div>
                    <div class="zn-fld zn-col-6">
                        <label for="personal-dialect">Dialect <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-dialect" id="personal-dialect"
                               value="{{ old('personal-dialect') }}">
                    </div>
                </div>

                <div class="zn-section mt-4"><h5>Government ID numbers</h5></div>
                <p class="zn-help">
                    If you're applying for your first job you may not have these yet — leave them
                    blank and add them later.
                </p>

                <div class="zn-grid">
                    <div class="zn-fld zn-col-3">
                        <label for="personal-sss">SSS number <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-sss" id="personal-sss" value="{{ old('personal-sss') }}">
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-hdmf">Pag-IBIG number <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-hdmf" id="personal-hdmf" value="{{ old('personal-hdmf') }}">
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-phic">PhilHealth number <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-phic" id="personal-phic" value="{{ old('personal-phic') }}">
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-tin">TIN <span class="zn-opt">optional</span></label>
                        <input type="text" name="personal-tin" id="personal-tin" value="{{ old('personal-tin') }}">
                    </div>
                </div>
            </section>

            {{-- ============ 6 · Password, then the acknowledgement ============ --}}
            <section class="zn-card zn-formcard zn-wstep" data-step="5" hidden>
                <div class="zn-section"><h5>Set a password</h5></div>
                <p class="zn-help">
                    You'll use this with your email or mobile number to sign back in and follow your
                    application.
                </p>

                <div class="zn-grid">
                    <div class="zn-fld zn-col-6">
                        <label for="app-code">Password <span class="zn-req">*</span></label>
                        <input type="password" name="app-code" id="app-code" autocomplete="new-password">
                        <p class="zn-fld-hint" id="pass-hint">At least 8 characters.</p>
                    </div>
                </div>

                <div class="zn-section mt-4"><h5>Before you submit</h5></div>

                {{-- The plain-language summary sits inline, on the same step as
                     the checkbox, rather than in a modal. Acknowledging
                     something you have to reopen a dialog to read is not an
                     informed acknowledgement. --}}
                <div class="zn-privacy-points">
                    <div class="zn-privacy-point">
                        <span class="zn-privacy-ico"><i class="bi bi-clipboard-check"></i></span>
                        <div>
                            <b>What we collect and why</b>
                            <span>Your personal details, background and documents, so we can assess and
                                process your application, contact you about it, and keep your profile for
                                future positions you apply to.</span>
                        </div>
                    </div>
                    <div class="zn-privacy-point">
                        <span class="zn-privacy-ico"><i class="bi bi-people"></i></span>
                        <div>
                            <b>Who sees it</b>
                            <span>The HR and recruitment personnel handling your application, and the
                                hiring managers for the role. We don't sell it or use it for marketing.</span>
                        </div>
                    </div>
                    <div class="zn-privacy-point">
                        <span class="zn-privacy-ico"><i class="bi bi-shield-check"></i></span>
                        <div>
                            <b>How it's handled</b>
                            <span>We process it under the Data Privacy Act of 2012 (RA 10173) and apply
                                measures intended to protect it against unauthorised access, loss or
                                misuse.</span>
                        </div>
                    </div>
                    <div class="zn-privacy-point">
                        <span class="zn-privacy-ico"><i class="bi bi-person-check"></i></span>
                        <div>
                            <b>Your rights stay yours</b>
                            <span>You can access your information, correct it, object to how it's used,
                                withdraw consent where consent is the basis, and complain to the National
                                Privacy Commission. Agreeing here waives none of that.</span>
                        </div>
                    </div>
                </div>

                <p class="zn-modal-foot">
                    That's a summary. Please read the
                    <a class="zn-link" href="{{ route('terms') }}" target="_blank" rel="noopener">Terms of Use</a>
                    and the
                    <a class="zn-link" href="{{ route('privacy') }}" target="_blank" rel="noopener">Data Privacy Notice</a>
                    in full before you agree.
                </p>

                {{-- The acknowledgement itself.

                     Scope is deliberately narrow: agreement to the Terms of Use,
                     and informed acknowledgement/consent for recruitment
                     processing under RA 10173. It is not a liability waiver and
                     does not purport to surrender any statutory right.

                     Not restored from old() on a validation bounce — a
                     resubmission is a fresh submission and should carry a fresh
                     affirmative act, not one inherited from the last attempt. --}}
                <label class="zn-check zn-check-lg" for="privacy-acknowledged">
                    <input type="checkbox" name="privacy-acknowledged" id="privacy-acknowledged" value="1">
                    <span>
                        I have read and understood the <b>Terms of Use</b> and the
                        <b>Data Privacy Notice</b>, and I agree to the Terms of Use. I understand how
                        my personal information will be collected, used, stored, accessed and
                        processed for recruitment purposes, and I give my consent to that processing
                        where consent is the applicable lawful basis.
                    </span>
                </label>
            </section>

            {{-- ============ Movement ============ --}}
            <div class="zn-formnav">
                <button type="button" class="zn-btn zn-btn-out" id="btn-prev" hidden>
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <span class="zn-formnav-hint" id="nav-hint">
                    <span class="zn-req">*</span> Required. You can change any of this later from your profile.
                </span>
                <button type="button" class="zn-btn" id="btn-next">
                    Continue <i class="bi bi-arrow-right"></i>
                </button>
                {{-- The final action. It exists only on the last step, directly
                     below the acknowledgement. --}}
                <button type="submit" class="zn-btn" id="btn-submit" hidden disabled>
                    Create my profile
                </button>
            </div>
        </form>

    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ---- Address cascade: province filters city, city filters barangay ----
       Preserved from the original implementation. Each address block has its
       own province/city/barangay trio, matched by the shared classes. */
    document.querySelectorAll('.select-province').forEach(function (selectProvince) {
        selectProvince.addEventListener('change', function () {
            const group = this.closest('.zn-grid');
            const selectCity = group.querySelector('.select-city');
            const selectBarangay = group.querySelector('.select-barangay');

            selectCity.value = '';
            selectBarangay.value = '';

            selectCity.querySelectorAll('option[province]').forEach(function (option) {
                option.style.display = option.getAttribute('province') === selectProvince.value ? '' : 'none';
            });
            selectBarangay.querySelectorAll('option[city]').forEach(function (option) {
                option.style.display = 'none';
            });
        });
    });

    document.querySelectorAll('.select-city').forEach(function (selectCity) {
        selectCity.addEventListener('change', function () {
            const group = this.closest('.zn-grid');
            const selectBarangay = group.querySelector('.select-barangay');

            selectBarangay.value = '';
            selectBarangay.querySelectorAll('option[city]').forEach(function (option) {
                option.style.display = option.getAttribute('city') === selectCity.value ? '' : 'none';
            });
        });
    });

    // Re-apply the filters on load so a validation bounce keeps its selections
    // visible rather than showing an empty city/barangay list.
    document.querySelectorAll('.select-province').forEach(function (p) {
        if (p.value) p.dispatchEvent(new Event('change'));
    });

    /* ---- Current address mirrors permanent ---- */
    const sameAs = document.getElementById('same-as-permanent');
    const pairs = [
        ['personal-padd-province', 'personal-cadd-province'],
        ['personal-padd-city', 'personal-cadd-city'],
        ['personal-padd-barangay', 'personal-cadd-barangay'],
        ['personal-padd-specific', 'personal-cadd-specific'],
    ];

    function mirror() {
        pairs.forEach(function ([from, to]) {
            const source = document.getElementById(from);
            const target = document.getElementById(to);

            if (target.tagName === 'SELECT') {
                target.querySelectorAll('option').forEach(function (option) {
                    if (option.value === source.value) option.style.display = '';
                });
            }

            target.value = source.value;
            target.disabled = sameAs.checked;
        });
    }

    sameAs.addEventListener('change', function () {
        if (sameAs.checked) {
            mirror();
        } else {
            pairs.forEach(([, to]) => { document.getElementById(to).disabled = false; });
        }
    });

    // Keep the mirror live while the box stays ticked.
    pairs.forEach(function ([from]) {
        document.getElementById(from).addEventListener('change', function () {
            if (sameAs.checked) mirror();
        });
    });

    // Disabled fields are not submitted, so release them just before send.
    document.getElementById('form-personal').addEventListener('submit', function () {
        pairs.forEach(([, to]) => { document.getElementById(to).disabled = false; });
    });

    /* ---- Age from birth date ---- */
    const birthdate = document.getElementById('personal-birthdate');
    const age = document.getElementById('personal-age');

    function computeAge() {
        if (!birthdate.value) { age.value = ''; return; }
        const born = new Date(birthdate.value);
        const now = new Date();
        let years = now.getFullYear() - born.getFullYear();
        const m = now.getMonth() - born.getMonth();
        if (m < 0 || (m === 0 && now.getDate() < born.getDate())) years--;
        age.value = years >= 0 && years < 130 ? years : '';
    }

    birthdate.addEventListener('change', computeAge);
    computeAge();

    /* ---- Stepping ----------------------------------------------------------
       Presentation only. Every step stays in the document, so the form still
       submits as one POST with every field, exactly as before. */
    const STEPS = @json(array_map(fn ($s) => ['title' => $s['title'], 'fields' => $s['fields']], $steps));
    const LAST = STEPS.length - 1;

    const panels   = Array.from(document.querySelectorAll('.zn-wstep'));
    const tabs     = Array.from(document.querySelectorAll('.zn-stepper-item[data-goto]'));
    const btnPrev  = document.getElementById('btn-prev');
    const btnNext  = document.getElementById('btn-next');
    const btnSend  = document.getElementById('btn-submit');
    const elNow    = document.getElementById('step-now');
    const elTitle  = document.getElementById('step-title');
    const elBar    = document.getElementById('step-bar');
    const navHint  = document.getElementById('nav-hint');
    const agree    = document.getElementById('privacy-acknowledged');
    const top      = document.getElementById('wizard-top');

    let current = 0;

    function show(index, scroll) {
        current = Math.max(0, Math.min(LAST, index));

        panels.forEach(function (panel, i) { panel.hidden = i !== current; });

        tabs.forEach(function (tab, i) {
            tab.classList.toggle('active', i === current);
            tab.classList.toggle('done', i < current);
            tab.setAttribute('aria-selected', i === current ? 'true' : 'false');
        });

        elNow.textContent = current + 1;
        elTitle.textContent = STEPS[current].title;
        elBar.style.width = Math.round((current + 1) / STEPS.length * 100) + '%';

        btnPrev.hidden = current === 0;
        btnNext.hidden = current === LAST;
        btnSend.hidden = current !== LAST;
        navHint.hidden = current === LAST;

        if (scroll !== false) {
            top.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    btnNext.addEventListener('click', function () { show(current + 1); });
    btnPrev.addEventListener('click', function () { show(current - 1); });

    // The numbered steps double as navigation — nothing here is gated, so an
    // applicant can jump back to fix one field without walking the whole form.
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () { show(Number(tab.dataset.goto)); });
    });

    /* ---- The acknowledgement gates the final action ---- */
    function syncAgree() { btnSend.disabled = !agree.checked; }
    agree.addEventListener('change', syncAgree);
    syncAgree();

    // Belt and braces: even if the button's disabled state were bypassed, the
    // form does not submit without the tick. The server rule is the real
    // guard — this only avoids a pointless round trip.
    document.getElementById('form-personal').addEventListener('submit', function (event) {
        if (!agree.checked) {
            event.preventDefault();
            show(LAST);
            agree.focus();
        }
    });

    /* ---- Land on the step that actually has the problem ---- */
    const ERRORS = @json(array_keys($errors->getMessages()));

    if (ERRORS.length) {
        const firstBad = STEPS.findIndex(function (step) {
            return step.fields.some(function (field) { return ERRORS.includes(field); });
        });
        show(firstBad === -1 ? 0 : firstBad, false);
    } else {
        show(0, false);
    }
});
</script>
@endpush
