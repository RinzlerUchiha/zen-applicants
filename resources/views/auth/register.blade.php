@extends('layouts.app')

@section('title', 'Create your applicant profile')

@section('body')
<main class="zn-canvas">
    <div class="zn-narrow">

        <div class="zn-formhead">
            <div>
                <p class="zn-page-title" style="margin-bottom:3px">Create your applicant profile</p>
                <p class="zn-page-sub" style="margin-bottom:0">
                    You only fill this in once — it carries over to every position you apply to.
                </p>
            </div>
            <span class="zn-count">Already have a profile?
                <a class="zn-link" href="{{ route('login') }}">Sign in</a></span>
        </div>

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

        <form id="form-personal" action="{{ route('register.store') }}" method="POST" novalidate>
            @csrf
            {{-- Set by the password modal after the privacy notice is acknowledged. --}}
            <input type="password" name="app-code" id="app-code" class="d-none" autocomplete="new-password">
            <input type="hidden" name="privacy-acknowledged" id="privacy-acknowledged" value="{{ old('privacy-acknowledged') }}">

            <fieldset class="border-0 p-0 m-0">

                {{-- ============ Position and name ============ --}}
                <section class="zn-card zn-formcard">
                    <div class="zn-section"><h5>The position</h5></div>

                    @if ($posting)
                        {{-- Taken from the posting the applicant clicked through
                             from. Not editable: the posting is the source of
                             truth, and a typed value could disagree with the
                             posting the application is actually attached to. --}}
                        <div class="zn-locked-field">
                            <div>
                                <span class="zn-locked-label">Applying for</span>
                                <b>{{ $posting->posting_title }}</b>
                                <span class="zn-locked-meta">
                                    This application will be linked to this posting.
                                    <a class="zn-link" href="{{ route('careers.show', $posting->id) }}"
                                       target="_blank" rel="noopener">View posting</a>
                                </span>
                            </div>
                            <span class="zn-pill zn-pill-acc"><i class="bi bi-lock-fill"></i> From posting</span>
                        </div>
                        <input type="hidden" name="position-applied" value="{{ $posting->posting_title }}">
                    @else
                        {{-- No posting chosen — they came straight to /apply. We
                             create the profile without a position rather than
                             inviting free text that matches no posting. --}}
                        <div class="zn-locked-field muted">
                            <div>
                                <span class="zn-locked-label">No position selected</span>
                                <b>You're creating a profile only</b>
                                <span class="zn-locked-meta">
                                    That's fine — you can apply to any position after this.
                                    <a class="zn-link" href="{{ route('careers.index') }}">Browse open positions</a>
                                </span>
                            </div>
                            <span class="zn-pill zn-pill-opt">Optional</span>
                        </div>
                    @endif

                    <div class="zn-section mt-4"><h5>Your name</h5></div>

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

                {{-- ============ Contact ============ --}}
                <section class="zn-card zn-formcard">
                    <div class="zn-section"><h5>How we reach you</h5></div>
                    <p class="zn-help">We'll use these to tell you about your application.</p>

                    <div class="zn-grid">
                        <div class="zn-fld zn-col-5">
                            <label for="personal-email">Email <span class="zn-req">*</span></label>
                            <input type="email" name="personal-email" id="personal-email"
                                   value="{{ old('personal-email') }}" placeholder="you@example.com">
                        </div>
                        <div class="zn-fld zn-col-4">
                            <label for="personal-contact">Mobile number <span class="zn-req">*</span></label>
                            <input type="text" name="personal-contact" id="personal-contact"
                                   value="{{ old('personal-contact') }}" placeholder="09#########">
                        </div>
                        <div class="zn-fld zn-col-3">
                            <label for="personal-telephone">Telephone <span class="zn-opt">optional</span></label>
                            <input type="text" name="personal-telephone" id="personal-telephone"
                                   value="{{ old('personal-telephone') }}">
                        </div>
                    </div>
                </section>

                {{-- ============ Addresses ============ --}}
                <section class="zn-card zn-formcard">
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
                </section>

                <section class="zn-card zn-formcard">
                    <div class="zn-formcard-head">
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

                <section class="zn-card zn-formcard">
                    <div class="zn-section"><h5>Place of birth</h5></div>

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

                {{-- ============ Basic info ============ --}}
                <section class="zn-card zn-formcard">
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

                        <div class="zn-fld zn-col-3">
                            <label for="personal-nationality">Nationality <span class="zn-req">*</span></label>
                            <input type="text" name="personal-nationality" id="personal-nationality"
                                   value="{{ old('personal-nationality') }}" placeholder="Filipino">
                        </div>
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

                        <div class="zn-fld zn-col-6">
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
                </section>

                {{-- ============ Government IDs ============ --}}
                <section class="zn-card zn-formcard">
                    <div class="zn-section"><h5>Government ID numbers</h5></div>
                    <p class="zn-help">
                        All optional. If you're applying for your first job you may not have these yet —
                        leave them blank and you can add them later.
                    </p>

                    <div class="zn-grid">
                        <div class="zn-fld zn-col-3">
                            <label for="personal-sss">SSS number</label>
                            <input type="text" name="personal-sss" id="personal-sss" value="{{ old('personal-sss') }}">
                        </div>
                        <div class="zn-fld zn-col-3">
                            <label for="personal-hdmf">Pag-IBIG number</label>
                            <input type="text" name="personal-hdmf" id="personal-hdmf" value="{{ old('personal-hdmf') }}">
                        </div>
                        <div class="zn-fld zn-col-3">
                            <label for="personal-phic">PhilHealth number</label>
                            <input type="text" name="personal-phic" id="personal-phic" value="{{ old('personal-phic') }}">
                        </div>
                        <div class="zn-fld zn-col-3">
                            <label for="personal-tin">TIN</label>
                            <input type="text" name="personal-tin" id="personal-tin" value="{{ old('personal-tin') }}">
                        </div>
                    </div>
                </section>

            </fieldset>

            <div class="zn-submitbar">
                <span class="zn-formnav-hint">
                    <span class="zn-req">*</span> Required. You can change any of this later from your profile.
                </span>
                <button type="button" class="zn-btn" id="btn-show-privacy">Create my profile</button>
            </div>
        </form>

    </div>
</main>

{{-- ============ Privacy notice, shown before anything is created ============ --}}
<div class="modal fade" id="privacyModal" data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content zn-modal">
            <div class="modal-header">
                <h1 class="modal-title" id="privacyModalLabel">Before you continue</h1>
            </div>
            <div class="modal-body">
                <p class="zn-modal-lede">
                    We're about to collect personal information from you. Here's what happens to it —
                    in plain terms.
                </p>

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
                            <span>We process it lawfully under the Data Privacy Act of 2012 (RA 10173) and
                                apply measures intended to protect it against unauthorised access, loss
                                or misuse.</span>
                        </div>
                    </div>
                    <div class="zn-privacy-point">
                        <span class="zn-privacy-ico"><i class="bi bi-person-check"></i></span>
                        <div>
                            <b>Your rights</b>
                            <span>You can access your information, correct it, object to how it's used,
                                and complain to the National Privacy Commission. You can view and edit
                                your profile any time after signing in.</span>
                        </div>
                    </div>
                </div>

                <p class="zn-modal-foot">
                    This is a summary. Please read the
                    <a class="zn-link" href="{{ route('privacy') }}" target="_blank" rel="noopener">full Privacy Notice</a>
                    for the complete details, including how to reach our Data Protection Officer.
                </p>

                <label class="zn-check zn-check-lg">
                    <input type="checkbox" id="privacy-agree">
                    <span>I've read and understood how my information will be collected and processed,
                        and I agree to continue.</span>
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="zn-btn zn-btn-sm" id="btn-privacy-continue" disabled>Continue</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ Password, after the notice is acknowledged ============ --}}
<div class="modal fade" id="setPassModal" data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-labelledby="setPassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content zn-modal">
            <div class="modal-header">
                <h1 class="modal-title" id="setPassModalLabel">Choose a password</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="zn-modal-lede">
                    You'll use this with your email or mobile number to sign back in and follow your
                    application.
                </p>
                <div class="zn-fld mb-0">
                    <label for="input-set-pass">Password</label>
                    <input type="password" id="input-set-pass" autocomplete="new-password">
                    <p class="zn-fld-hint" id="pass-hint">At least 8 characters.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" data-bs-dismiss="modal">Back</button>
                <button type="button" class="zn-btn zn-btn-sm" id="btn-set-pass">Create profile</button>
            </div>
        </div>
    </div>
</div>
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

    /* ---- Privacy notice, then password ----
       The notice is shown before any account is created, so acknowledgement
       happens at the point of collection rather than after the fact. */
    const privacyModal = new bootstrap.Modal(document.getElementById('privacyModal'));
    const passModal = new bootstrap.Modal(document.getElementById('setPassModal'));
    const agree = document.getElementById('privacy-agree');
    const btnContinue = document.getElementById('btn-privacy-continue');

    document.getElementById('btn-show-privacy').addEventListener('click', function () {
        privacyModal.show();
    });

    agree.addEventListener('change', function () {
        btnContinue.disabled = !agree.checked;
    });

    btnContinue.addEventListener('click', function () {
        if (!agree.checked) return;
        document.getElementById('privacy-acknowledged').value = '1';
        privacyModal.hide();
        passModal.show();
    });

    /* ---- Set password and submit ---- */
    const inputPass = document.getElementById('input-set-pass');
    const passHint = document.getElementById('pass-hint');

    document.getElementById('btn-set-pass').addEventListener('click', function () {
        if (inputPass.value.length < 8) {
            passHint.textContent = 'Please use at least 8 characters.';
            passHint.style.color = 'var(--zn-warn)';
            inputPass.focus();
            return;
        }

        document.getElementById('app-code').value = inputPass.value;
        document.getElementById('form-personal').submit();
    });

    inputPass.addEventListener('input', function () {
        passHint.textContent = 'At least 8 characters.';
        passHint.style.color = '';
    });
});
</script>
@endpush
