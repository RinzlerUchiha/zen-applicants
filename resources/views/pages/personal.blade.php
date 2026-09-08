@extends('layouts.layout')

@section('content')

    <style>
        #form-personal {
            font-size: 16px;
        }

        /* ── Section cards with left border ───────────────────────── */
        .info-section {
            border-left: 3px solid #0d6efd;
            padding-left: 1rem;
            margin-bottom: 1.75rem;
        }

        .info-section>h6 {
            color: #0d6efd;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .65rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        /* ── Field label / value ───────────────────────────────────── */
        .field-label {
            color: #6c757d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 1px;
        }

        #form-personal input,
        #form-personal select {
            font-weight: 600;
            min-height: 1.1em;
            font-size: 15px;
        }

        #form-personal fieldset:disabled input,
        #form-personal fieldset:disabled select {
            border: none !important;
            padding: 0 !important;
            background: transparent;
            appearance: none;
            -webkit-appearance: none;
        }

        #form-personal fieldset:not(:disabled) input,
        #form-personal fieldset:not(:disabled) select {
            border-bottom: 1px solid #0d6efd !important;
            background: #f4f8ff;
            padding: 2px 4px !important;
        }

        /* ── Copy button ────────────────────────────────────────────── */
        .btn-copy {
            padding: 0 3px;
            border: none;
            background: transparent;
            color: #adb5bd;
            cursor: pointer;
            font-size: 11px;
            vertical-align: middle;
            transition: color .15s;
        }

        .btn-copy:hover {
            color: #0d6efd;
        }

        /* ── Mask toggle ────────────────────────────────────────────── */
        .btn-mask-toggle {
            padding: 0 4px;
            border: none;
            background: transparent;
            color: #6c757d;
            cursor: pointer;
            font-size: 10px;
            vertical-align: middle;
            transition: color .15s;
        }

        .btn-mask-toggle:hover {
            color: #0d6efd;
        }

        .masked {
            letter-spacing: 1px;
            color: #adb5bd;
        }

        /* ── Status badge ───────────────────────────────────────────── */
        .status-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 2px 8px;
            border-radius: 20px;
        }

        /* ── Accordion tweaks ───────────────────────────────────────── */
        .info-accordion {
            --bs-accordion-bg: #e9ecf3;
        }

        .info-accordion .accordion-button {
            font-size: 14px;
            font-weight: 600;
            color: #495057;
            background: transparent;
            box-shadow: none;
            padding: 6px 0;
        }

        .info-accordion .accordion-button:not(.collapsed) {
            color: #0d6efd;
            background: transparent;
        }

        .info-accordion .accordion-button::after {
            width: 12px;
            height: 12px;
            background-size: 12px;
        }

        .info-accordion .accordion-item {
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-accordion .accordion-body {
            padding: 6px 0 12px 0;
        }

        /* ── Copy toast ─────────────────────────────────────────────── */
        #copy-toast {
            z-index: 9999;
            display: none;
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
        }

        #personal-img-preview:hover {
            transform: scale(1.7);
        }

        /* ── Print ──────────────────────────────────────────────────── */
        @media print {

            #sidebar,
            .btn-copy,
            .btn-mask-toggle,
            #btn-print,
            nav,
            .offcanvas,
            .dropdown,
            button:not(.accordion-button),
            #hireModal,
            #profile-tabs,
            #btn-edit-personal,
            #btn-cancel-personal,
            [type="submit"] {
                display: none !important;
            }

            .info-section {
                border-left-color: #000 !important;
                page-break-inside: avoid;
            }

            .info-accordion .accordion-collapse {
                display: block !important;
            }

            body,
            #form-personal {
                font-size: 10px;
            }

            .masked {
                color: #000 !important;
                letter-spacing: normal;
            }
        }
    </style>

    <script type="text/javascript">
        $(function() {
            $('.select-province').change(function() {
                let select = $(this).closest('.row').find('.select-city');
                select.find('option').not('[value=""]').hide();
                select.find('option[province="' + this.value + '"]').show();
            });

            $('.select-city').change(function() {
                let select = $(this).closest('.row').find('.select-barangay');
                select.find('option').not('[value=""]').hide();
                select.find('option[city="' + this.value + '"]').show();
            });

            // Re-run the cascade on page load so the saved city/barangay
            // option is unhidden immediately — matches what happens once
            // the user interacts with the dropdowns in edit mode.
            $('.select-province').trigger('change');
            $('.select-city').trigger('change');

            $('#btn-edit-personal').click(function() {
                $('#form-personal fieldset').prop('disabled', false);
                $('#form-personal [type="submit"]').toggleClass('d-none');
                $('#btn-cancel-personal').toggleClass('d-none');
                $(this).toggleClass('d-none');
            });

            $('#btn-cancel-personal').click(function() {
                $('#form-personal fieldset').prop('disabled', true);
                $('#form-personal #btn-edit-personal').toggleClass('d-none');
                $('#form-personal [type="submit"]').toggleClass('d-none');
                $(this).toggleClass('d-none');
            });

            let imgInput = document.getElementById('personal-img-input');
            let imgpreview = document.getElementById('personal-img-preview');

            imgpreview.addEventListener('click', () => imgInput.click());

            imgInput.addEventListener('change', () => {
                const file = imgInput.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);

                fetch('/profile/img', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            imgpreview.src = URL.createObjectURL(file);
                        } else {
                            alert('Upload failed.');
                        }
                    })
                    .catch(err => {
                        console.error('Upload error:', err);
                        alert('Upload error.');
                    });
            });
        });

        // ── Copy to clipboard ──────────────────────────────────────────
        function copyText(text) {
            const done = () => {
                const t = document.getElementById('copy-toast');
                t.style.display = 'block';
                setTimeout(() => t.style.display = 'none', 2000);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                const el = document.createElement('textarea');
                el.value = text;
                el.style.position = 'fixed';
                el.style.opacity = '0';
                document.body.appendChild(el);
                el.focus();
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                done();
            }
        }

        // ── Government ID masking ───────────────────────────────────────
        let govVisible = false;
        const govIds = ['gov-sss', 'gov-pagibig', 'gov-philhealth', 'gov-tin'];

        function toggleGovIds() {
            govVisible = !govVisible;
            govIds.forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.textContent = govVisible ? el.dataset.real : el.dataset.masked;
                el.classList.toggle('masked', !govVisible);
            });
            document.getElementById('gov-toggle-icon').className =
                govVisible ? 'bi bi-eye' : 'bi bi-eye-slash';
            document.getElementById('gov-toggle-label').textContent =
                govVisible ? 'Hide' : 'Show';
        }
    </script>

    <div class="container-fluid">
        <form id="form-personal" class="mb-3" action="{{ route('personal.store') }}" method="POST">
            @if (session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @csrf
            <input type="file" id="personal-img-input" accept="image/*" style="display:none;">
            <fieldset disabled>

                {{-- ── Header row: photo · name · status · print ─────────── --}}
                <div class="d-flex align-items-start gap-3 mb-4">
                    <img id="personal-img-preview"
                        src="{{ $user?->app_img ? route('file.get', ['src' => 'applicant', 'filename' => $user?->app_img]) : asset('no-file.png') }}"
                        class="img-thumbnail object-fit-contain flex-shrink-0" alt="Applicant photo"
                        style="cursor: pointer; height:70px; width:70px;">

                    <div class="flex-grow-1 min-w-0">
                        <div class="row g-2 align-items-center mb-1">
                            <div class="col-auto">
                                <input type="text" class="form-control-plaintext d-inline-block fw-bold fs-6"
                                    style="width: 130px;" name="personal-firstname" id="personal-firstname"
                                    value="{{ $user?->app_fname }}" placeholder="First Name">
                            </div>
                            <div class="col-auto">
                                <input type="text" class="form-control-plaintext d-inline-block fw-bold fs-6"
                                    style="width: 130px;" name="personal-middlename" id="personal-middlename"
                                    value="{{ $user?->app_mname }}" placeholder="Middle Name">
                            </div>
                            <div class="col-auto">
                                <input type="text" class="form-control-plaintext d-inline-block fw-bold fs-6"
                                    style="width: 130px;" name="personal-lastname" id="personal-lastname"
                                    value="{{ $user?->app_lname }}" placeholder="Last Name">
                            </div>
                            <div class="col-auto">
                                <input type="text" class="form-control-plaintext d-inline-block text-muted"
                                    style="width: 80px;" name="personal-suffix" id="personal-suffix"
                                    value="{{ $user?->app_suffix ?? '' }}" placeholder="Suffix">
                            </div>
                        </div>

                        <div class="text-muted mb-1 d-flex align-items-center gap-2" style="font-size:14px;">
                            Applied for:
                            <input type="text" class="form-control-plaintext d-inline-block fw-bold"
                                style="width: 200px;" name="position-applied" id="position-applied"
                                value="{{ $user?->app_posapplied }}">
                        </div>

                        @php
                            $status = strtolower($user?->app_status ?? '');
                            $badgeCss = match ($status) {
                                'active' => 'background:#d1fae5; color:#065f46;',
                                'hired' => 'background:#dbeafe; color:#1e40af;',
                                'inactive' => 'background:#f3f4f6; color:#6b7280;',
                                default => 'background:#fef9c3; color:#92400e;',
                            };
                        @endphp
                        <span class="status-badge" style="{{ $badgeCss }}">
                            {{ $status ? ucfirst($status) : 'Pending' }}
                        </span>

                        @if ($user?->app_date)
                            <div class="text-muted mt-1" style="font-size:12px;">
                                Record created: {{ \Carbon\Carbon::parse($user->app_date)->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── Contact Info ────────────────────────────────────────── --}}
                <div class="info-section">
                    <h6><i class="bi bi-person-lines-fill"></i> Contact Info</h6>
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="field-label">Email</div>
                            <div class="d-flex align-items-center gap-1">
                                <input type="email" class="form-control-plaintext" name="personal-email"
                                    id="personal-email" value="{{ $user?->app_email }}">
                                @if ($user?->app_email)
                                    <button type="button" class="btn-copy"
                                        onclick="copyText(document.getElementById('personal-email').value)"
                                        title="Copy email">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="field-label">Personal Contact</div>
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control-plaintext" name="personal-contact"
                                    id="personal-contact" value="{{ $user?->app_mobile }}">
                                @if ($user?->app_mobile)
                                    <button type="button" class="btn-copy"
                                        onclick="copyText(document.getElementById('personal-contact').value)"
                                        title="Copy number">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg">
                            <div class="field-label">Telephone</div>
                            <input type="text" class="form-control-plaintext" name="personal-telephone"
                                id="personal-telephone" value="{{ $user?->app_telephone }}">
                        </div>
                    </div>
                </div>
            </fieldset>

            {{-- ── Address (accordion) ─────────────────────────────────── --}}
            <div class="info-section">
                <h6><i class="bi bi-geo-alt-fill"></i> Address</h6>
                <div class="accordion info-accordion" id="accordionAddress">

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#addrPerm">
                                Permanent Address
                            </button>
                        </h2>
                        <div id="addrPerm" class="accordion-collapse collapse show" data-bs-parent="#accordionAddress">
                            <div class="accordion-body">
                                <fieldset disabled>
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <div class="field-label">Province</div>
                                        <select class="form-control-plaintext select-province"
                                            name="personal-padd-province" id="personal-padd-province">
                                            <option value {{ !$user?->address?->add_perm_prov ? 'selected' : '' }}>-Select-
                                            </option>
                                            @foreach ($provinceList as $list)
                                                <option value="{{ $list->pr_name }}"
                                                    {{ $user?->address?->add_perm_prov == $list->pr_name ? 'selected' : '' }}>
                                                    {{ $list->pr_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="field-label">City / Municipality</div>
                                        <select class="form-control-plaintext select-city" name="personal-padd-city"
                                            id="personal-padd-city">
                                            <option value {{ !$user?->address?->add_perm_city ? 'selected' : '' }}>-Select-
                                            </option>
                                            @foreach ($municipalityList as $list)
                                                <option
                                                    style="{{ $list->ct_province_name != $user?->address?->add_perm_prov ? 'display: none;' : '' }}"
                                                    province="{{ $list->ct_province_name }}"
                                                    value="{{ $list->ct_name }}"
                                                    {{ $user?->address?->add_perm_city == $list->ct_name ? 'selected' : '' }}>
                                                    {{ $list->ct_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="field-label">Barangay</div>
                                        <select class="form-control-plaintext select-barangay"
                                            name="personal-padd-barangay" id="personal-padd-barangay">
                                            <option value {{ !$user?->address?->add_perm_brngy ? 'selected' : '' }}>
                                                -Select-</option>
                                            @foreach ($barangayList as $list)
                                                <option
                                                    style="{{ $list->br_city_name != $user?->address?->add_perm_city ? 'display: none;' : '' }}"
                                                    city="{{ $list->br_city_name }}" value="{{ $list->br_name }}"
                                                    {{ $user?->address?->add_perm_brngy == $list->br_name ? 'selected' : '' }}>
                                                    {{ $list->br_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="field-label">Street / House #</div>
                                        <input type="text" class="form-control-plaintext"
                                            name="personal-padd-specific" id="personal-padd-specific"
                                            value="{{ $user?->address?->add_perm_location }}">
                                    </div>
                                </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#addrCur">
                                Current Address
                            </button>
                        </h2>
                        <div id="addrCur" class="accordion-collapse collapse" data-bs-parent="#accordionAddress">
                            <div class="accordion-body">
                                <fieldset disabled>
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <div class="field-label">Province</div>
                                        <select class="form-control-plaintext select-province"
                                            name="personal-cadd-province" id="personal-cadd-province">
                                            <option value {{ !$user?->address?->add_cur_prov ? 'selected' : '' }}>-Select-
                                            </option>
                                            @foreach ($provinceList as $list)
                                                <option value="{{ $list->pr_name }}"
                                                    {{ $user?->address?->add_cur_prov == $list->pr_name ? 'selected' : '' }}>
                                                    {{ $list->pr_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="field-label">City / Municipality</div>
                                        <select class="form-control-plaintext select-city" name="personal-cadd-city"
                                            id="personal-cadd-city">
                                            <option value {{ !$user?->address?->add_cur_city ? 'selected' : '' }}>-Select-
                                            </option>
                                            @foreach ($municipalityList as $list)
                                                <option
                                                    style="{{ $list->ct_province_name != $user?->address?->add_cur_prov ? 'display: none;' : '' }}"
                                                    province="{{ $list->ct_province_name }}"
                                                    value="{{ $list->ct_name }}"
                                                    {{ $user?->address?->add_cur_city == $list->ct_name ? 'selected' : '' }}>
                                                    {{ $list->ct_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="field-label">Barangay</div>
                                        <select class="form-control-plaintext select-barangay"
                                            name="personal-cadd-barangay" id="personal-cadd-barangay">
                                            <option value {{ !$user?->address?->add_cur_brngy ? 'selected' : '' }}>-Select-
                                            </option>
                                            @foreach ($barangayList as $list)
                                                <option
                                                    style="{{ $list->br_city_name != $user?->address?->add_cur_city ? 'display: none;' : '' }}"
                                                    city="{{ $list->br_city_name }}" value="{{ $list->br_name }}"
                                                    {{ $user?->address?->add_cur_brngy == $list->br_name ? 'selected' : '' }}>
                                                    {{ $list->br_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="field-label">Street / House #</div>
                                        <input type="text" class="form-control-plaintext"
                                            name="personal-cadd-specific" id="personal-cadd-specific"
                                            value="{{ $user?->address?->add_cur_location }}">
                                    </div>
                                </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#addrBirth">
                                Place of Birth
                            </button>
                        </h2>
                        <div id="addrBirth" class="accordion-collapse collapse" data-bs-parent="#accordionAddress">
                            <div class="accordion-body">
                                <fieldset disabled>
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <div class="field-label">Province</div>
                                        <select class="form-control-plaintext select-province"
                                            name="personal-badd-province" id="personal-badd-province">
                                            <option value {{ !$user?->address?->add_birth_prov ? 'selected' : '' }}>
                                                -Select-</option>
                                            @foreach ($provinceList as $list)
                                                <option value="{{ $list->pr_name }}"
                                                    {{ $user?->address?->add_birth_prov == $list->pr_name ? 'selected' : '' }}>
                                                    {{ $list->pr_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="field-label">City / Municipality</div>
                                        <select class="form-control-plaintext select-city" name="personal-badd-city"
                                            id="personal-badd-city">
                                            <option value {{ !$user?->address?->add_birth_city ? 'selected' : '' }}>
                                                -Select-</option>
                                            @foreach ($municipalityList as $list)
                                                <option
                                                    style="{{ $list->ct_province_name != $user?->address?->add_birth_prov ? 'display: none;' : '' }}"
                                                    province="{{ $list->ct_province_name }}"
                                                    value="{{ $list->ct_name }}"
                                                    {{ $user?->address?->add_birth_city == $list->ct_name ? 'selected' : '' }}>
                                                    {{ $list->ct_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="field-label">Barangay</div>
                                        <select class="form-control-plaintext select-barangay"
                                            name="personal-badd-barangay" id="personal-badd-barangay">
                                            <option value {{ !$user?->address?->add_birth_brngy ? 'selected' : '' }}>
                                                -Select-</option>
                                            @foreach ($barangayList as $list)
                                                <option
                                                    style="{{ $list->br_city_name != $user?->address?->add_birth_city ? 'display: none;' : '' }}"
                                                    city="{{ $list->br_city_name }}" value="{{ $list->br_name }}"
                                                    {{ $user?->address?->add_birth_brngy == $list->br_name ? 'selected' : '' }}>
                                                    {{ $list->br_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="field-label">Street / House #</div>
                                        <input type="text" class="form-control-plaintext"
                                            name="personal-badd-specific" id="personal-badd-specific"
                                            value="{{ $user?->address?->add_birth_location }}">
                                    </div>
                                </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        <fieldset disabled>
            {{-- ── Basic Info ──────────────────────────────────────────── --}}
            <div class="info-section">
                <h6><i class="bi bi-info-circle-fill"></i> Basic Info</h6>
                <div class="row g-3">
                    <div class="col-lg-auto">
                        <div class="field-label">Birth Date</div>
                        <input type="date" class="form-control-plaintext" name="personal-birthdate"
                            id="personal-birthdate" value="{{ $user?->app_bdate }}">
                    </div>
                    <div class="col-lg-1">
                        <div class="field-label">Age</div>
                        <input type="text" readonly class="form-control-plaintext" id="personal-age"
                            value="{{ $user?->app_age }}">
                    </div>
                    <div class="col-lg-auto">
                        <div class="field-label">Civil Status</div>
                        <select class="form-control-plaintext" name="personal-civil-status" id="personal-civil-status">
                            <option value {{ !$user?->app_cstatus ? 'selected' : '' }}>-Select-</option>
                            <option value="Single" {{ $user?->app_cstatus == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ $user?->app_cstatus == 'Married' ? 'selected' : '' }}>Married
                            </option>
                            <option value="Separated/Divorced"
                                {{ $user?->app_cstatus == 'Separated/Divorced' ? 'selected' : '' }}>Separated/Divorced
                            </option>
                            <option value="Widow/Widower" {{ $user?->app_cstatus == 'Widow/Widower' ? 'selected' : '' }}>
                                Widow/Widower</option>
                        </select>
                    </div>
                    <div class="col-lg-auto">
                        <div class="field-label">Sex</div>
                        <select class="form-control-plaintext" name="personal-sex" id="personal-sex">
                            <option value {{ !$user?->app_sex ? 'selected' : '' }}>-Select-</option>
                            <option value="Male" {{ $user?->app_sex == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ $user?->app_sex == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="col-lg-auto">
                        <div class="field-label">Blood Type</div>
                        <select class="form-control-plaintext" name="personal-bloodtype" id="personal-bloodtype">
                            <option value {{ !$user?->app_btype ? 'selected' : '' }}>-Select-</option>
                            <option value="O+" {{ $user?->app_btype == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ $user?->app_btype == 'O-' ? 'selected' : '' }}>O-</option>
                            <option value="A+" {{ $user?->app_btype == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ $user?->app_btype == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ $user?->app_btype == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ $user?->app_btype == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ $user?->app_btype == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ $user?->app_btype == 'AB-' ? 'selected' : '' }}>AB-</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <div class="field-label">Height (cm)</div>
                        <input type="text" class="form-control-plaintext" name="personal-height" id="personal-height"
                            value="{{ $user?->app_height }}">
                    </div>
                    <div class="col-lg-2">
                        <div class="field-label">Weight (kg)</div>
                        <input type="text" class="form-control-plaintext" name="personal-weight" id="personal-weight"
                            value="{{ $user?->app_weight }}">
                    </div>
                    <div class="col-lg-auto">
                        <div class="field-label">Nationality</div>
                        <input type="text" class="form-control-plaintext" name="personal-nationality"
                            id="personal-nationality" value="{{ $user?->app_nationality }}">
                    </div>
                    <div class="col-lg-auto">
                        <div class="field-label">Religion</div>
                        <input type="text" class="form-control-plaintext" name="personal-religion"
                            id="personal-religion" value="{{ $user?->app_religion }}">
                    </div>
                    <div class="col-lg-auto">
                        <div class="field-label">Dialect</div>
                        <input type="text" class="form-control-plaintext" name="personal-dialect"
                            id="personal-dialect" value="{{ $user?->app_dialect }}">
                    </div>
                </div>
            </div>

            </fieldset>

            {{-- ── Government IDs ──────────────────────────────────────── --}}
            <div class="info-section">
                <h6>
                    <i class="bi bi-shield-lock-fill"></i> Government IDs
                    <button type="button" class="btn-mask-toggle" id="gov-toggle-btn" onclick="toggleGovIds()"
                        title="Show / hide all IDs">
                        <i class="bi bi-eye-slash" id="gov-toggle-icon"></i>
                        <span id="gov-toggle-label">Show</span>
                    </button>
                </h6>
                <div class="row g-3">
                    @foreach ([['SSS #', $user?->app_sss, 'gov-sss', 'personal-sss'], ['Pagibig #', $user?->app_pagibig, 'gov-pagibig', 'personal-hdmf'], ['Philhealth #', $user?->app_philhealth, 'gov-philhealth', 'personal-phic'], ['TIN #', $user?->app_tin, 'gov-tin', 'personal-tin']] as [$label, $raw, $govId, $inputName])
                        <div class="col-lg-3">
                            <div class="field-label">{{ $label }}</div>
                            <input type="hidden" name="{{ $inputName }}" id="{{ $inputName }}"
                                value="{{ $raw }}">
                            @if ($raw)
                                <span id="{{ $govId }}" class="masked" data-real="{{ $raw }}"
                                    data-masked="{{ str_repeat('●', max(0, strlen($raw) - 3)) . substr($raw, -3) }}">
                                    {{ str_repeat('●', max(0, strlen($raw) - 3)) . substr($raw, -3) }}
                                </span>
                            @else
                                <span class="field-value">—</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3 mb-5">
                <button id="btn-print" type="button" class="btn btn-sm btn-outline-secondary me-2"
                    onclick="window.print()" title="Print profile">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button type="button" id="btn-edit-personal" class="btn btn-outline-secondary btn-sm">Edit</button>
                <button type="button" id="btn-cancel-personal" class="btn btn-danger btn-sm mx-1 d-none">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm mx-1 d-none">Save</button>
            </div>
        </form>
    </div>

    {{-- ── Copy toast ──────────────────────────────────────────────────── --}}
    <div id="copy-toast">
        <div class="toast show align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body py-2">
                    <i class="bi bi-check-circle me-1"></i> Copied to clipboard
                </div>
            </div>
        </div>
    </div>

@stop