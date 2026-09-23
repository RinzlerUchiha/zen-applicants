{{--
    HR's access code — the assessment gate. Used on the Assessments page and on
    an assessment that is locked, interrupted, or running in another browser.
    It is an access code HR hands over in person, not an SMS/email OTP.

    Access opens assessments for a limited time. When it has ended, this says
    so, and — for an applicant HR has already given a code — offers to ask HR
    for a new one (recorded for HR in zen-admin; nothing is sent).

    $assessmentKey (optional): the assessment this is shown on.
--}}
@php
    $access = config('application_form.assessments.access');
    $gate = app(\App\Services\AssessmentGate::class);
    $me = auth()->user();
    $lastUnlock = $gate->lastUnlock($me);
    $openRequest = $gate->openRequest($me);
    $canRequest = $gate->canRequest($me);
@endphp

@if ($lastUnlock && !$gate->isUnlocked($me))
    <p class="zn-assess-ended" role="status">
        <i class="bi bi-clock-history" aria-hidden="true"></i>
        @if ($lastUnlock->unlocked_until && $lastUnlock->unlocked_until->isFuture() && !$lastUnlock->revoked_at)
            <span>Your access is open in another browser until {{ $lastUnlock->unlocked_until->format('g:i A') }}.
                To continue in this browser, you need a new code from HR.</span>
        @else
            <span>Your access ended{{ $lastUnlock->unlocked_until ? ' at ' . $lastUnlock->unlocked_until->format('M j, g:i A') : '' }}.
                An assessment you were already taking was not cut off by this — but starting the next one needs a new code from HR.</span>
        @endif
    </p>
@endif

<form method="POST" action="{{ route('assessments.access') }}" class="zn-assess-code">
    @csrf
    <label for="assess-code">Access code from HR</label>
    <div class="zn-assess-code-row">
        <input type="text" id="assess-code" name="code" inputmode="numeric" autocomplete="one-time-code"
               maxlength="{{ $access['code_length'] + 2 }}" placeholder="{{ str_repeat('•', $access['code_length']) }}"
               class="zn-input @error('code') is-invalid @enderror" value=""
               aria-describedby="assess-code-help @error('code') assess-code-error @enderror"
               @error('code') aria-invalid="true" autofocus @enderror required>
        <button type="submit" class="zn-btn">Unlock</button>
    </div>
    @error('code')
        <p class="zn-field-error" id="assess-code-error" role="alert">{{ $message }}</p>
    @enderror
    <p class="zn-muted" id="assess-code-help">
        A {{ $access['code_length'] }}-digit code, valid for {{ $access['code_minutes'] }} minutes after HR gives it to you.
        It works once, in this browser only, and opens the assessments for {{ $access['unlock_hours'] }} hours.
    </p>
</form>

@if ($canRequest)
    <form method="POST" action="{{ route('assessments.access.request') }}" class="zn-assess-request">
        @csrf
        @if (!empty($assessmentKey))
            <input type="hidden" name="assessment" value="{{ $assessmentKey }}">
        @endif
        @if ($openRequest)
            <p class="zn-muted">
                <i class="bi bi-send-check" aria-hidden="true"></i>
                You asked HR for a new code at {{ $openRequest->requested_at->format('g:i A') }}{{ $openRequest->requested_at->isToday() ? '' : ' on ' . $openRequest->requested_at->format('M j') }}.
                When they give it to you, enter it above.
                <button type="submit" class="zn-link">Ask again</button>
            </p>
        @else
            <p class="zn-muted" style="margin-bottom:6px">Need a new code? HR will see your request and give you one.</p>
            <button type="submit" class="zn-btn zn-btn-out zn-btn-sm"><i class="bi bi-send" aria-hidden="true"></i> Ask HR for a new code</button>
        @endif
    </form>
@endif
@error('access_request')
    <p class="zn-field-error" role="alert">{{ $message }}</p>
@enderror
