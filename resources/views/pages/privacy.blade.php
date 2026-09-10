@extends('layouts.app')

@section('title', 'Privacy Notice')

@section('body')
<main class="zn-canvas">
    <div class="zn-narrower">

        <p class="zn-page-title">Privacy Notice for Applicants</p>
        <p class="zn-page-sub">
            How we collect and handle the information you give us through this Careers Portal,
            under the Data Privacy Act of 2012 (Republic Act No. 10173).
        </p>

        @php
            $dpo = config('privacy.dpo');
            $retention = config('privacy.retention');
            $basis = config('privacy.lawful_basis');
            $pending = collect([$dpo, $retention, $basis])->contains(fn ($item) => $item['confirm'] ?? false);
        @endphp

        @if ($pending)
            {{-- Visible on purpose. A notice missing its DPO contact, retention
                 period or lawful basis is not compliant, and hiding that behind
                 placeholder prose would be worse than admitting it. --}}
            <div class="zn-toast error" style="display:block">
                <b><i class="bi bi-exclamation-triangle-fill"></i> Draft — not yet approved for use</b>
                <div style="font-weight:400;margin-top:4px">
                    Parts of this notice still need to be supplied by HR and the Data Protection
                    Officer. They are marked below and must not be guessed at.
                </div>
            </div>
        @endif

        <div class="zn-card zn-prose">
            <h2>Who collects your information</h2>
            <p>
                {{ config('privacy.controller.name') }} is responsible for the personal information
                you submit through this portal.
                @if (config('privacy.controller.confirm'))
                    <span class="zn-confirm">Confirm the exact registered entity name.</span>
                @endif
            </p>

            <h2>What we collect</h2>
            <ul>
                @foreach (config('privacy.collected') as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

            <h2>Why we collect it</h2>
            <ul>
                @foreach (config('privacy.purposes') as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <p>
                We do not use your information for anything unrelated to recruitment and employment
                without telling you first.
            </p>

            <h2>Our basis for processing</h2>
            @if ($basis['statement'])
                <p>{{ $basis['statement'] }}</p>
            @else
                <p class="zn-confirm-block">
                    <b>To be confirmed by legal.</b> The lawful criterion relied on under Section 12
                    of RA 10173 — and Section 13 for any sensitive personal information — must be
                    stated here. This has deliberately not been drafted, because choosing a basis is
                    a legal determination, not a wording exercise.
                </p>
            @endif

            <h2>Who can see it</h2>
            <p>
                Your information is accessible to the HR and recruitment personnel involved in
                assessing your application, and to the hiring managers for the position you applied
                to. We do not sell your information or share it for marketing.
            </p>

            <h2>How long we keep it</h2>
            @if ($retention['statement'])
                <p>{{ $retention['statement'] }}</p>
            @else
                <p class="zn-confirm-block">
                    <b>To be confirmed by HR.</b> The retention period for unsuccessful applicants
                    and for hired employees must be stated here. RA 10173 requires that personal
                    information be kept only as long as necessary for the stated purpose.
                </p>
            @endif

            <h2>How we protect it</h2>
            <p>
                We apply organisational, physical and technical measures intended to protect your
                information against unauthorised access, loss or misuse — including restricting
                access to authorised personnel and transmitting your data over encrypted
                connections. No system can be guaranteed absolutely secure, and we do not claim
                otherwise; if a breach affecting your information occurs, we will act in accordance
                with our obligations under the Act.
            </p>

            <h2>Your rights</h2>
            <p>Under RA 10173 you have the right:</p>
            <ul>
                @foreach (config('privacy.rights') as $right)
                    <li>{{ $right }}</li>
                @endforeach
            </ul>
            <p>
                You can exercise most of these directly in this portal — your profile can be viewed
                and corrected at any time after you sign in.
            </p>

            <h2>How to reach us</h2>
            @if ($dpo['email'] || $dpo['name'])
                <p>
                    Our Data Protection Officer{{ $dpo['name'] ? ', ' . $dpo['name'] . ',' : '' }}
                    can be reached at
                    @if ($dpo['email'])<a class="zn-link" href="mailto:{{ $dpo['email'] }}">{{ $dpo['email'] }}</a>@endif
                    @if ($dpo['phone']) or {{ $dpo['phone'] }}@endif.
                </p>
            @else
                <p class="zn-confirm-block">
                    <b>To be confirmed.</b> RA 10173 requires a designated Data Protection Officer
                    and a working contact route for data subjects. Name, email address and contact
                    number must be supplied before this notice is published.
                </p>
            @endif
            <p>
                If you believe your rights have been violated, you may also complain to the
                <a class="zn-link" href="{{ config('privacy.npc.website') }}" target="_blank" rel="noopener">{{ config('privacy.npc.name') }}</a>.
            </p>
        </div>

        <p class="zn-landing-foot">
            <a class="zn-link" href="{{ url()->previous() }}">&larr; Go back</a>
        </p>

    </div>
</main>
@endsection
