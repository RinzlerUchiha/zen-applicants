@extends('layouts.layout')

@section('title', 'Documents')

@section('content')

<p class="zn-page-title">Documents</p>
<p class="zn-page-sub" style="max-width:62ch">
    {{ $required->count() }} items are required for your application. We accept
    {{ strtoupper(implode(', ', $extensions)) }} up to {{ round($maxSizeKb / 1024) }} MB.
    A photo taken with your phone is fine, as long as the text is readable. Uploading a document you
    have already sent replaces it.
</p>

{{-- What HR is waiting on, first. Everything here is also shown against the
     document itself below; this is the summary so it cannot be missed. --}}
@if ($attention->isNotEmpty())
    <div class="zn-missing">
        <i class="bi bi-exclamation-circle-fill"></i>
        <div>
            <b>HR needs {{ $attention->count() === 1 ? 'something' : $attention->count() . ' things' }} from you</b>
            <span>
                @foreach ($attention as $slot)
                    {{ $slot['document']?->review_status === 'rejected' ? 'Replace your' : 'Send your' }}
                    {{ strtolower($slot['label']) }}@if ($slot['request_for']) for {{ $slot['request_for'] }}@endif{{ $loop->last ? '.' : ';' }}
                @endforeach
            </span>
        </div>
    </div>
@endif

{{-- One upload path: each document's own Upload / Replace button. The type
     comes from the button pressed, so a file can never be sent as the wrong
     document. Same POST and field names as before. --}}
<form id="form-document" method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="d-none">
    @csrf
    <input type="hidden" name="doc_type" id="doc_type" value="">
    <input type="file" name="doc_file" id="doc_file" accept=".{{ implode(',.', $extensions) }}" tabindex="-1">
</form>

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

@endsection

@push('scripts')
<script>
    $(function () {
        let pressed = null;

        // A document's Upload / Replace button sets the type and opens the file
        // picker. Choosing a file sends it straight away — the type is already
        // decided by the button, so there is nothing left to ask.
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
