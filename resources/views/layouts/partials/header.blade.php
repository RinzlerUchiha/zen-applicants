{{--
    The one header, used signed-in and signed-out.

    Each destination appears here once, under one name. Page-specific actions —
    applying for a particular job, creating a profile from the landing page —
    live in the page, where they carry their context.
--}}
<header class="zn-topbar">
    <a class="zn-brand" href="{{ auth()->check() ? route('home') : route('landing') }}">
        <img src="https://teamtngc.com/zen/assets/img/coffi.png" width="26" height="26" alt="">
        ZenHub <em>{{ auth()->check() ? 'Applicants' : 'Careers' }}</em>
    </a>

    @auth
        <nav class="zn-topnav" aria-label="Main">
            {{-- My Applications is part of Home: Home lists the applications and
                 links to the full list, so it is not a second top-level item. --}}
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home', 'applications.*') ? 'active' : '' }}">Home</a>
            <a href="{{ route('personal.show') }}"
               class="{{ request()->routeIs('personal.*', 'family.*', 'education.*', 'employment.*', 'skill.*', 'license.*', 'certificate.*', 'characterref.*') ? 'active' : '' }}">Application Form</a>
            <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.*') ? 'active' : '' }}">Documents</a>
            <a href="{{ route('assessments.index') }}"
               class="{{ request()->routeIs('assessments.*', 'enneagram.*', 'tapt.*', 'disc.*', 'miq.*', 'color.*', 'vak.*', 'why_i_work.*', 'career_anchors.*', 'abstract_reasoning.*', 'basic_math.*', 'maya.*') ? 'active' : '' }}">Assessments</a>
            <a href="{{ route('careers.index') }}" class="{{ request()->routeIs('careers.*') ? 'active' : '' }}">Open positions</a>
        </nav>

        <div class="zn-user dropdown">
            <div class="zn-user-meta d-none d-md-block">
                <div class="zn-user-name">{{ auth()->user()->first_last_name }}</div>
                <div class="zn-user-role">Applicant</div>
            </div>
            <button class="zn-avatar border-0" type="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-expanded="false" aria-label="Account menu">
                {{ strtoupper(mb_substr(auth()->user()->app_fname ?? 'A', 0, 1) . mb_substr(auth()->user()->app_lname ?? '', 0, 1)) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Sign out</button>
                    </form>
                </li>
            </ul>
        </div>
    @else
        <nav class="zn-topnav" aria-label="Main">
            <a href="{{ route('careers.index') }}" class="{{ request()->routeIs('careers.*') ? 'active' : '' }}">Open positions</a>
        </nav>

        {{-- Sign in is the one account action in the header. It is hidden on the
             sign-in page itself, where the form is already the page. --}}
        @unless (request()->routeIs('login'))
            <div class="d-flex align-items-center gap-2">
                <a class="zn-btn zn-btn-out zn-btn-sm" href="{{ route('login') }}">Sign in</a>
            </div>
        @endunless
    @endauth
</header>
