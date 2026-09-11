@extends('layouts.app')

@section('title', 'Terms of Use')

@section('body')
<main class="zn-canvas">
    <div class="zn-narrower">

        <p class="zn-page-title">Terms of Use</p>
        <p class="zn-page-sub">
            The rules for using this Careers Portal and for applying through it.
            Version {{ config('terms.version') }}.
        </p>

        @php
            $entity = config('terms.entity');
            $law = config('terms.governing_law');
            $pending = ($entity['confirm'] ?? false) || ($law['confirm'] ?? false);
        @endphp

        @if ($pending)
            {{-- Same stance as the privacy notice: say out loud that this is a
                 draft rather than dress a gap up as settled wording. --}}
            <div class="zn-toast error" style="display:block">
                <b><i class="bi bi-exclamation-triangle-fill"></i> Draft — not yet approved for use</b>
                <div style="font-weight:400;margin-top:4px">
                    Parts of these terms still need to be supplied by legal. They are marked below.
                </div>
            </div>
        @endif

        <div class="zn-card zn-prose">
            <h2>Who these terms are with</h2>
            <p>
                These terms apply between you and {{ $entity['name'] }} when you use this Careers
                Portal.
                @if ($entity['confirm'])
                    <span class="zn-confirm">Confirm the exact registered entity name.</span>
                @endif
            </p>
            <p>
                They cover how the portal is used. How your personal information is handled is
                covered separately by the
                <a class="zn-link" href="{{ route('privacy') }}">Privacy Notice</a>, which forms part
                of these terms.
            </p>

            <h2>Using the portal</h2>
            <ul>
                @foreach (config('terms.using_the_portal') as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

            <h2>The information you give us</h2>
            <ul>
                @foreach (config('terms.your_information') as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

            <h2>Applications</h2>
            <ul>
                @foreach (config('terms.applications') as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

            <h2>Availability</h2>
            <p>{{ config('terms.availability') }}</p>

            <h2>Your rights are not affected</h2>
            {{-- Stated explicitly so the acknowledgement cannot be read as a
                 waiver. RA 10173 rights are statutory and survive these terms. --}}
            <p>
                Nothing in these terms takes away your rights under the Data Privacy Act of 2012
                (Republic Act No. 10173). You keep the right to be informed, to access and correct
                your information, to object to processing, to withdraw consent where consent is the
                basis relied on, and to complain to the
                <a class="zn-link" href="{{ config('privacy.npc.website') }}" target="_blank" rel="noopener">{{ config('privacy.npc.name') }}</a>.
                Agreeing to these terms is not a waiver of any of them, and does not relieve us of
                our obligations under the Act.
            </p>

            <h2>Governing law</h2>
            @if ($law['statement'])
                <p>{{ $law['statement'] }}</p>
            @else
                <p class="zn-confirm-block">
                    <b>To be confirmed by legal.</b> The governing law and the venue for disputes
                    must be stated here. This has deliberately not been drafted, because choosing a
                    forum is a legal determination.
                </p>
            @endif

            <h2>Changes to these terms</h2>
            <p>{{ config('terms.changes') }}</p>
        </div>

        <p class="zn-landing-foot">
            <a class="zn-link" href="{{ url()->previous() }}">&larr; Go back</a>
        </p>

    </div>
</main>
@endsection
