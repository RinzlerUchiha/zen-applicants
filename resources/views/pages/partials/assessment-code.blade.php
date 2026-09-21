{{--
    HR's access code — the assessment gate. Used on the Assessments page and on
    an assessment that is locked, interrupted, or running in another browser.
    It is an access code HR hands over in person, not an SMS/email OTP.
--}}
@php $access = config('application_form.assessments.access'); @endphp
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
