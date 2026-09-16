@extends('layouts.form-section')

@section('title', 'Personal details')

@section('section')

@php
    // Government IDs are masked by default. They are not required to apply,
    // but they belong to the eventual 201 record, so they are collected here
    // and shown only on request.
    $govIds = [
        ['SSS number',        $user?->app_sss,        'gov-sss',        'personal-sss'],
        ['Pag-IBIG number',   $user?->app_pagibig,    'gov-pagibig',    'personal-hdmf'],
        ['PhilHealth number', $user?->app_philhealth, 'gov-philhealth', 'personal-phic'],
        ['TIN',               $user?->app_tin,        'gov-tin',        'personal-tin'],
    ];

    $addressBlocks = [
        ['key' => 'padd', 'label' => 'Permanent address', 'required' => true],
        ['key' => 'cadd', 'label' => 'Current address',   'required' => true],
        ['key' => 'badd', 'label' => 'Place of birth',    'required' => false],
    ];
@endphp

{{-- Edit sits at the top, where it is the first thing an applicant sees on
     their own profile. It sticks while editing so Save is always reachable. --}}
<div class="zn-page-toolbar" id="personal-toolbar">
    <p class="zn-help">
        Your own details. This is the only section that is not a list — everything here is about you.
    </p>
    <div class="zn-page-toolbar-actions">
        <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="btn-cancel-personal" data-unsaved-discard hidden>Cancel</button>
        <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="btn-edit-personal">
            <i class="bi bi-pencil"></i> Edit details
        </button>
        <button type="submit" form="form-personal" class="zn-btn zn-btn-sm" id="btn-save-personal" hidden>Save changes</button>
    </div>
</div>

{{-- Who you are, and what you applied for. Deliberately outside the form: the
     photo saves on its own, so changing it does not mean editing the profile.
     The positions come from the applications themselves, and there can be
     several of them. --}}
<section class="zn-card zn-formcard">
    <div class="zn-identity">
        <div class="zn-identity-photo">
            @if ($user?->app_img)
                <img id="personal-img-preview" src="{{ url('/file/app-img/' . $user->app_img) }}" alt="">
            @else
                <span class="zn-identity-initials" id="personal-img-preview">
                    {{ strtoupper(mb_substr($user?->app_fname ?? 'A', 0, 1) . mb_substr($user?->app_lname ?? '', 0, 1)) }}
                </span>
            @endif
            <button type="button" class="zn-link zn-photo-btn" id="btn-change-photo">
                {{ $user?->app_img ? 'Change photo' : 'Add photo' }}
            </button>
            <input type="file" id="personal-img-input" accept="image/*" class="d-none">
            <p class="zn-photo-status" id="personal-img-status" hidden></p>
        </div>

        <div class="zn-identity-main">
            <h2 class="zn-identity-name">
                {{ trim(($user?->app_fname ?? '') . ' ' . ($user?->app_mname ?? '') . ' ' . ($user?->app_lname ?? '') . ' ' . ($user?->app_suffix ?? '')) ?: 'Your name' }}
            </h2>

            @if (($appliedPositions ?? collect())->isNotEmpty())
                <div class="zn-identity-meta zn-applied-for">
                    <span>Applied for</span>
                    <ul class="zn-applied-list">
                        @foreach ($appliedPositions as $application)
                            <li title="Applied {{ $application['applied_at']?->format('F j, Y') }}">
                                <b>{{ $application['title'] }}</b>
                                <span class="zn-pill zn-pill-acc">{{ $application['status'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @elseif ($user?->app_posapplied)
                <p class="zn-identity-meta">Applied for <b>{{ $user->app_posapplied }}</b></p>
            @endif

            @if ($user?->app_date)
                <p class="zn-identity-meta">
                    Profile created {{ \Carbon\Carbon::parse($user->app_date)->format('F j, Y') }}
                </p>
            @endif
        </div>
    </div>
</section>

<form id="form-personal" data-unsaved-guard method="POST" action="{{ route('personal.store') }}">
    @csrf
    <fieldset id="personal-fieldset" disabled class="border-0 p-0 m-0">

        {{-- ---------- Your name ---------- --}}
        <section class="zn-card zn-formcard">
            <div class="zn-section"><h5>Your name</h5></div>
            <div class="zn-grid">
                <div class="zn-fld zn-col-4">
                    <label for="personal-firstname">First name <span class="zn-req">*</span></label>
                    <input type="text" name="personal-firstname" id="personal-firstname" value="{{ $user?->app_fname }}">
                </div>
                <div class="zn-fld zn-col-4">
                    <label for="personal-middlename">Middle name <span class="zn-opt">optional</span></label>
                    <input type="text" name="personal-middlename" id="personal-middlename" value="{{ $user?->app_mname }}">
                </div>
                <div class="zn-fld zn-col-4">
                    <label for="personal-lastname">Last name <span class="zn-req">*</span></label>
                    <input type="text" name="personal-lastname" id="personal-lastname" value="{{ $user?->app_lname }}">
                </div>
                <div class="zn-fld zn-col-3">
                    <label for="personal-suffix">Suffix <span class="zn-opt">optional</span></label>
                    <input type="text" name="personal-suffix" id="personal-suffix" value="{{ $user?->app_suffix }}">
                </div>
            </div>
        </section>

        {{-- ---------- Contact ---------- --}}
        <section class="zn-card zn-formcard">
            <div class="zn-section"><h5>How we reach you</h5></div>
            <div class="zn-grid">
                <div class="zn-fld zn-col-5">
                    <label for="personal-email">Email <span class="zn-req">*</span></label>
                    <input type="email" name="personal-email" id="personal-email" value="{{ $user?->app_email }}">
                </div>
                <div class="zn-fld zn-col-4">
                    <label for="personal-contact">Mobile number <span class="zn-req">*</span></label>
                    <input type="text" name="personal-contact" id="personal-contact" value="{{ $user?->app_mobile }}">
                </div>
                <div class="zn-fld zn-col-3">
                    <label for="personal-telephone">Telephone <span class="zn-opt">optional</span></label>
                    <input type="text" name="personal-telephone" id="personal-telephone" value="{{ $user?->app_telephone }}">
                </div>
            </div>
        </section>

        {{-- ---------- Addresses ---------- --}}
        @foreach ($addressBlocks as $block)
            @php $k = $block['key']; @endphp
            <section class="zn-card zn-formcard">
                <div class="zn-formcard-head">
                    <div class="zn-section mb-0"><h5>{{ $block['label'] }}</h5></div>
                    @if ($k === 'cadd')
                        <label class="zn-check">
                            <input type="checkbox" id="same-as-permanent">
                            <span>Same as permanent address</span>
                        </label>
                    @endif
                </div>

                <div class="zn-grid">
                    <div class="zn-fld zn-col-3">
                        <label for="personal-{{ $k }}-province">Province
                            @if ($block['required'])<span class="zn-req">*</span>@else<span class="zn-opt">optional</span>@endif</label>
                        <select class="select-province" name="personal-{{ $k }}-province" id="personal-{{ $k }}-province">
                            <option value="">Select province</option>
                            @foreach ($provinceList as $p)
                                <option value="{{ $p->pr_name }}" @selected($user?->address?->{'add_' . ($k === 'padd' ? 'perm' : ($k === 'cadd' ? 'cur' : 'birth')) . '_prov'} === $p->pr_name)>{{ $p->pr_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-{{ $k }}-city">City / Municipality
                            @if ($block['required'])<span class="zn-req">*</span>@else<span class="zn-opt">optional</span>@endif</label>
                        <select class="select-city" name="personal-{{ $k }}-city" id="personal-{{ $k }}-city">
                            <option value="">Select city</option>
                            @foreach ($municipalityList as $m)
                                <option style="display:none" province="{{ $m->ct_province_name }}"
                                        value="{{ $m->ct_name }}" @selected($user?->address?->{'add_' . ($k === 'padd' ? 'perm' : ($k === 'cadd' ? 'cur' : 'birth')) . '_city'} === $m->ct_name)>{{ $m->ct_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-{{ $k }}-barangay">Barangay
                            @if ($block['required'])<span class="zn-req">*</span>@else<span class="zn-opt">optional</span>@endif</label>
                        <select class="select-barangay" name="personal-{{ $k }}-barangay" id="personal-{{ $k }}-barangay">
                            <option value="">Select barangay</option>
                            @foreach ($barangayList as $b)
                                <option style="display:none" city="{{ $b->br_city_name }}"
                                        value="{{ $b->br_name }}" @selected($user?->address?->{'add_' . ($k === 'padd' ? 'perm' : ($k === 'cadd' ? 'cur' : 'birth')) . '_brngy'} === $b->br_name)>{{ $b->br_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="zn-fld zn-col-3">
                        <label for="personal-{{ $k }}-specific">Street / House no.
                            @if ($block['required'])<span class="zn-req">*</span>@else<span class="zn-opt">optional</span>@endif</label>
                        <input type="text" name="personal-{{ $k }}-specific" id="personal-{{ $k }}-specific"
                               value="{{ $user?->address?->{'add_' . ($k === 'padd' ? 'perm' : ($k === 'cadd' ? 'cur' : 'birth')) . '_location'} }}">
                    </div>
                </div>
            </section>
        @endforeach

        {{-- ---------- About you ---------- --}}
        <section class="zn-card zn-formcard">
            <div class="zn-section"><h5>About you</h5></div>
            <div class="zn-grid">
                <div class="zn-fld zn-col-3">
                    <label for="personal-birthdate">Birth date <span class="zn-req">*</span></label>
                    <input type="date" name="personal-birthdate" id="personal-birthdate" value="{{ $user?->app_bdate }}">
                </div>
                <div class="zn-fld zn-col-2">
                    <label for="personal-age">Age</label>
                    <input type="text" id="personal-age" readonly tabindex="-1" value="{{ $user?->app_age }}" placeholder="—">
                </div>
                <div class="zn-fld zn-col-4">
                    <label for="personal-civil-status">Civil status <span class="zn-req">*</span></label>
                    <select name="personal-civil-status" id="personal-civil-status">
                        <option value="">Select</option>
                        @foreach (['Single', 'Married', 'Separated/Divorced', 'Widow/Widower'] as $o)
                            <option value="{{ $o }}" @selected($user?->app_cstatus === $o)>{{ $o }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="zn-fld zn-col-3">
                    <label for="personal-sex">Sex <span class="zn-req">*</span></label>
                    <select name="personal-sex" id="personal-sex">
                        <option value="">Select</option>
                        @foreach (['Male', 'Female'] as $o)
                            <option value="{{ $o }}" @selected($user?->app_sex === $o)>{{ $o }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="zn-fld zn-col-3">
                    <label for="personal-nationality">Nationality <span class="zn-req">*</span></label>
                    <input type="text" name="personal-nationality" id="personal-nationality" value="{{ $user?->app_nationality }}">
                </div>
                <div class="zn-fld zn-col-3">
                    <label for="personal-bloodtype">Blood type <span class="zn-opt">optional</span></label>
                    <select name="personal-bloodtype" id="personal-bloodtype">
                        <option value="">Select</option>
                        @foreach (['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $o)
                            <option value="{{ $o }}" @selected($user?->app_btype === $o)>{{ $o }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="zn-fld zn-col-3">
                    <label for="personal-height">Height (cm) <span class="zn-opt">optional</span></label>
                    <input type="text" name="personal-height" id="personal-height" value="{{ $user?->app_height }}">
                </div>
                <div class="zn-fld zn-col-3">
                    <label for="personal-weight">Weight (kg) <span class="zn-opt">optional</span></label>
                    <input type="text" name="personal-weight" id="personal-weight" value="{{ $user?->app_weight }}">
                </div>

                <div class="zn-fld zn-col-6">
                    <label for="personal-religion">Religion <span class="zn-opt">optional</span></label>
                    <input type="text" name="personal-religion" id="personal-religion" value="{{ $user?->app_religion }}">
                </div>
                <div class="zn-fld zn-col-6">
                    <label for="personal-dialect">Dialect <span class="zn-opt">optional</span></label>
                    <input type="text" name="personal-dialect" id="personal-dialect" value="{{ $user?->app_dialect }}">
                </div>
            </div>
        </section>

        {{-- ---------- Government IDs ---------- --}}
        <section class="zn-card zn-formcard">
            <div class="zn-formcard-head">
                <div class="zn-section mb-0"><h5>Government ID numbers</h5></div>
                <button type="button" class="zn-link" id="gov-toggle-btn" style="font-size:12px">
                    <i class="bi bi-eye-slash" id="gov-toggle-icon"></i>
                    <span id="gov-toggle-label">Show</span>
                </button>
            </div>
            <p class="zn-help">
                All optional now. You'll need these if you're hired, so adding them early saves a step later.
            </p>

            <div class="zn-grid">
                @foreach ($govIds as [$label, $raw, $govId, $inputName])
                    <div class="zn-fld zn-col-3">
                        <label for="{{ $inputName }}">{{ $label }} <span class="zn-opt">optional</span></label>
                        <input type="text" name="{{ $inputName }}" id="{{ $inputName }}"
                               class="gov-input" data-real="{{ $raw }}"
                               value="{{ $raw ? str_repeat('•', min(mb_strlen($raw), 12)) : '' }}"
                               data-masked="{{ $raw ? str_repeat('•', min(mb_strlen($raw), 12)) : '' }}">
                    </div>
                @endforeach
            </div>
        </section>

    </fieldset>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ---- Read mode by default, edit on request ----
       The fieldset is disabled until Edit is pressed, so the page opens as a
       readable summary rather than a wall of active inputs. */
    const fieldset = document.getElementById('personal-fieldset');
    const btnEdit = document.getElementById('btn-edit-personal');
    const btnCancel = document.getElementById('btn-cancel-personal');
    const btnSave = document.getElementById('btn-save-personal');
    const form = document.getElementById('form-personal');

    function setEditing(on) {
        fieldset.disabled = !on;
        btnEdit.hidden = on;
        btnCancel.hidden = !on;
        btnSave.hidden = !on;
        document.body.classList.toggle('zn-editing', on);
        document.getElementById('personal-toolbar').classList.toggle('is-editing', on);
    }

    btnEdit.addEventListener('click', () => setEditing(true));
    btnCancel.addEventListener('click', () => window.location.reload());
    setEditing(false);

    /* ---- Government ID masking ----
       Values are masked in the input itself; revealing swaps in the real value
       so it can still be edited. */
    let govVisible = false;
    const govInputs = document.querySelectorAll('.gov-input');

    document.getElementById('gov-toggle-btn').addEventListener('click', function () {
        govVisible = !govVisible;

        govInputs.forEach(function (input) {
            if (!input.dataset.real) return;
            input.value = govVisible ? input.dataset.real : input.dataset.masked;
        });

        document.getElementById('gov-toggle-icon').className = govVisible ? 'bi bi-eye' : 'bi bi-eye-slash';
        document.getElementById('gov-toggle-label').textContent = govVisible ? 'Hide' : 'Show';
    });

    // A masked value must never be saved back over the real one.
    form.addEventListener('submit', function () {
        govInputs.forEach(function (input) {
            if (input.value === input.dataset.masked && input.dataset.real) {
                input.value = input.dataset.real;
            }
        });
    });

    // Typing replaces the mask outright rather than appending to dots.
    govInputs.forEach(function (input) {
        input.addEventListener('focus', function () {
            if (input.value === input.dataset.masked && input.dataset.real) {
                input.value = input.dataset.real;
                govVisible = true;
            }
        });
    });

    /* ---- Address cascade ---- */
    document.querySelectorAll('.select-province').forEach(function (selectProvince) {
        selectProvince.addEventListener('change', function () {
            const group = this.closest('.zn-grid');
            const city = group.querySelector('.select-city');
            const barangay = group.querySelector('.select-barangay');

            city.value = '';
            barangay.value = '';

            city.querySelectorAll('option[province]').forEach(function (o) {
                o.style.display = o.getAttribute('province') === selectProvince.value ? '' : 'none';
            });
            barangay.querySelectorAll('option[city]').forEach(o => { o.style.display = 'none'; });
        });
    });

    document.querySelectorAll('.select-city').forEach(function (selectCity) {
        selectCity.addEventListener('change', function () {
            const barangay = this.closest('.zn-grid').querySelector('.select-barangay');
            barangay.value = '';
            barangay.querySelectorAll('option[city]').forEach(function (o) {
                o.style.display = o.getAttribute('city') === selectCity.value ? '' : 'none';
            });
        });
    });

    // Reveal the options matching already-saved values on load, otherwise a
    // saved city would sit in a list where every option is hidden.
    document.querySelectorAll('.zn-grid').forEach(function (group) {
        const province = group.querySelector('.select-province');
        const city = group.querySelector('.select-city');
        const barangay = group.querySelector('.select-barangay');
        if (!province) return;

        if (province.value) {
            city?.querySelectorAll('option[province]').forEach(function (o) {
                o.style.display = o.getAttribute('province') === province.value ? '' : 'none';
            });
        }
        if (city?.value) {
            barangay?.querySelectorAll('option[city]').forEach(function (o) {
                o.style.display = o.getAttribute('city') === city.value ? '' : 'none';
            });
        }
    });

    /* ---- Current address mirrors permanent ---- */
    const sameAs = document.getElementById('same-as-permanent');
    const pairs = [
        ['personal-padd-province', 'personal-cadd-province'],
        ['personal-padd-city', 'personal-cadd-city'],
        ['personal-padd-barangay', 'personal-cadd-barangay'],
        ['personal-padd-specific', 'personal-cadd-specific'],
    ];

    if (sameAs) {
        sameAs.addEventListener('change', function () {
            pairs.forEach(function ([from, to]) {
                const src = document.getElementById(from);
                const dst = document.getElementById(to);

                if (sameAs.checked) {
                    dst.querySelectorAll('option').forEach(function (o) {
                        if (o.value === src.value) o.style.display = '';
                    });
                    dst.value = src.value;
                }
                dst.readOnly = sameAs.checked && dst.tagName !== 'SELECT';
            });
        });
    }

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

    /* ---- Profile photo ---- */
    const photoInput = document.getElementById('personal-img-input');
    document.getElementById('btn-change-photo').addEventListener('click', () => photoInput.click());

    const photoStatus = document.getElementById('personal-img-status');

    function say(message, failed) {
        photoStatus.textContent = message;
        photoStatus.classList.toggle('is-error', !!failed);
        photoStatus.hidden = false;
    }

    // Swaps the new photo in where the old photo (or the initials) was, so the
    // applicant sees what was saved without the page reloading under them.
    function showPhoto(url) {
        const current = document.getElementById('personal-img-preview');
        const img = document.createElement('img');
        img.id = 'personal-img-preview';
        img.alt = '';
        img.src = url + '?v=' + Date.now();
        current.replaceWith(img);
        document.getElementById('btn-change-photo').textContent = 'Change photo';
    }

    photoInput.addEventListener('change', function () {
        if (!photoInput.files.length) return;

        const data = new FormData();
        data.append('image', photoInput.files[0]);
        data.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        say('Saving photo\u2026');

        fetch(@json(route('file.store')), {
            method: 'POST',
            body: data,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(response => response.json().catch(() => ({})))
            .then(function (result) {
                if (!result.success) {
                    say(result.error || 'That photo could not be saved. Please try again.', true);
                    return;
                }
                showPhoto(result.url);
                say('Photo saved.');
            })
            .catch(() => say('That photo could not be uploaded. Please try another file.', true))
            .finally(() => { photoInput.value = ''; });
    });
});
</script>
@endpush
