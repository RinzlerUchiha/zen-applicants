@extends('layouts.guest')

@section('title', 'Apply · ' . $posting->posting_title)

@section('content')

{{--
    The apply step. An application is sent with a CV/résumé and a 2x2 picture
    (required) and, optionally, a cover letter — nothing from the later
    pre-employment list. Documents already on file are the applicant's own and
    are reused; uploading here is the same upload as the Documents page, and
    it comes back to this page.
--}}

<p style="margin:0 0 12px">
    <a class="zn-link" href="{{ route('careers.show', $posting->id) }}">&larr; Back to the posting</a>
</p>

@if (session('info'))
    <div class="zn-toast"><i class="bi bi-info-circle-fill"></i> {{ session('info') }}</div>
@endif

<p class="zn-crumb">Apply</p>
<p class="zn-page-title">{{ $posting->posting_title }}</p>
<p class="zn-page-sub" style="max-width:62ch">
    Send your application with a CV and a 2x2 picture. A cover letter is optional. We accept
    {{ strtoupper(implode(', ', $extensions)) }} up to {{ round($maxSizeKb / 1024) }} MB — a clear phone photo is fine.
    Documents you have already sent are used for this application too.
</p>

<form id="form-document" method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="d-none">
    @csrf
    <input type="hidden" name="for_posting" value="{{ $posting->id }}">
    <input type="hidden" name="doc_type" id="doc_type" value="">
    <input type="file" name="doc_file" id="doc_file" accept=".{{ implode(',.', $extensions) }}" tabindex="-1">
</form>

<section class="zn-card zn-formcard">
    <div class="zn-section"><h5>1 · Application materials</h5></div>

    <p class="zn-group-label">Required</p>
    <div class="zn-doc-list">
        @foreach ($required as $slot)
            @include('pages.partials.document-slot', ['slot' => $slot])
        @endforeach
    </div>

    <p class="zn-group-label">Optional</p>
    <div class="zn-doc-list">
        @foreach ($optional as $slot)
            @include('pages.partials.document-slot', ['slot' => $slot])
        @endforeach
    </div>
</section>

<section class="zn-card zn-formcard">
    <div class="zn-section"><h5>2 · Your application details</h5></div>
    @if ($nextSection)
        <p class="zn-help" style="margin:0 0 10px">
            Your application form is {{ $percent }}% complete. You can submit now and finish it afterwards — HR
            reviews the complete form, so it helps to fill it in soon.
        </p>
        <a class="zn-btn zn-btn-out zn-btn-sm" href="{{ Route::has($nextSection['route']) ? route($nextSection['route']) : route('home') }}">
            Continue your application form: {{ strtolower($nextSection['label']) }}
        </a>
    @else
        <p class="zn-help" style="margin:0"><i class="bi bi-check-circle-fill" style="color:var(--zn-ok)"></i>
            Your application form is complete. It is sent with this application.</p>
    @endif
</section>

<div class="zn-apply-bar">
    <div class="zn-apply-bar-inner">
        <div class="zn-apply-bar-text">
            <b>{{ $posting->posting_title }}</b>
            @if ($missing)
                <span>Still needed: {{ implode(', ', $missing) }}</span>
            @else
                <span>Your CV and 2x2 picture are ready to send</span>
            @endif
        </div>
        <form method="POST" action="{{ route('careers.apply', $posting->id) }}" class="m-0">
            @csrf
            <button type="submit" class="zn-btn" @disabled($missing)>Submit application</button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function () {
        let pressed = null;

        // Same upload as the Documents page: the button pressed decides the
        // document type, and choosing a file sends it straight away.
        $('.js-pick').on('click', function () {
            pressed = $(this);
            $('#doc_type').val(pressed.data('type'));
            $('#doc_file').val('').trigger('click');
        });

        $('#doc_file').on('change', function () {
            if (!this.files.length || !$('#doc_type').val()) return;

            $('.js-pick').prop('disabled', true);
            if (pressed) pressed.text('Uploading…');
            $('#form-document').trigger('submit');
        });
    });
</script>
@endpush
