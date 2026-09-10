{{--
    The one header, used signed-in and signed-out.

    Kept at this path because both existing layouts already @include it, so
    nothing else has to change to pick up the new shell.
--}}
<header class="zn-topbar">
    <a class="zn-brand" href="{{ auth()->check() ? route('home') : route('landing') }}">
        <img src="https://teamtngc.com/zen/assets/img/coffi.png" width="26" height="26" alt="">
        ZenHub <em>{{ auth()->check() ? 'Applicants' : 'Careers' }}</em>
    </a>

    @auth
        <nav class="zn-topnav" aria-label="Main">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">My Application</a>
            <a href="{{ route('personal.show') }}"
               class="{{ request()->routeIs('personal.*', 'family.*', 'education.*', 'employment.*', 'skill.*', 'license.*', 'certificate.*', 'characterref.*') ? 'active' : '' }}">Application Form</a>
            <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.*') ? 'active' : '' }}">Documents</a>
            <a href="{{ route('assessments.index') }}"
               class="{{ request()->routeIs('assessments.*', 'enneagram.*', 'tapt.*', 'disc.*', 'miq.*', 'color.*', 'vak.*', 'why_i_work.*', 'career_anchors.*', 'abstract_reasoning.*', 'basic_math.*', 'maya.*') ? 'active' : '' }}">Assessments</a>
            <a href="{{ route('applications.index') }}" class="{{ request()->routeIs('applications.*') ? 'active' : '' }}">My Applications</a>
            <a href="{{ route('careers.index') }}" class="{{ request()->routeIs('careers.*') ? 'active' : '' }}">Browse Jobs</a>
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
                <li><a class="dropdown-item" href="{{ route('personal.show') }}"><i class="bi bi-person"></i> My profile</a></li>
                <li><hr class="dropdown-divider"></li>
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

        <div class="d-flex align-items-center gap-2">
            <a class="zn-btn zn-btn-out zn-btn-sm" href="{{ route('login') }}">Sign in</a>
            <a class="zn-btn zn-btn-sm" href="{{ route('register') }}">Apply now</a>
        </div>
    @endauth
</header>
