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

{{-- The run HR has started, if there is one: one deadline and one allowance for
     everything asked for, not one per document. Shown before the documents,
     because it is the thing with a clock on it. --}}
@if ($process)
    @if ($process->is_active)
        <div class="zn-deadline">
            <div class="zn-deadline-main">
                <b>
                    @if ($process->days_left === 0)
                        Due today
                    @else
                        {{ $process->days_left }} {{ Str::plural('day', $process->days_left) }} left
                    @endif
                </b>
                <span>
                    Send everything below by <b>{{ $process->deadline_at->format('F j, Y') }}</b>.
                    This one date covers every document we have asked for.
                    @if ($process->attempts_left <= 1)
                        You have <b>{{ $process->attempts_left }}</b> replacement
                        {{ Str::plural('attempt', $process->attempts_left) }} left.
                    @endif
                </span>
            </div>
            <button type="button" class="zn-link zn-deadline-out" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                Withdraw
            </button>
        </div>
    @else
        <div class="zn-toast {{ $process->status === 'complete' ? '' : 'error' }}">
            <i class="bi {{ $process->status === 'complete' ? 'bi-check-circle-fill' : 'bi-info-circle-fill' }}"></i>
            <div>
                <b>{{ $process->status_label }}</b>
                <span>
                    @switch($process->status)
                        @case('complete')
                            Everything we asked for has been accepted. We will be in touch about the next step.
                            @break
                        @case('non_responsive')
                            The deadline passed before we received everything. Your details stay on file,
                            and we may consider you for other openings.
                            @break
                        @case('requirements_not_met')
                            We were not able to accept the documents within the attempts allowed. Your details
                            stay on file, and we may consider you for other openings.
                            @break
                        @case('withdrawn')
                            You withdrew from this process. Your details stay on file, and you are welcome
                            to apply again.
                            @break
                    @endswitch
                </span>
            </div>
        </div>
    @endif
@endif

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

{{-- Withdrawing is deliberate and is confirmed, because it ends the process.
     Nothing is deleted: the details stay on file. --}}
@if ($process?->is_active)
    <div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content zn-modal" method="POST" action="{{ route('documents.withdraw') }}">
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title" id="withdrawModalTitle">Withdraw from this process?</h1>
                </div>
                <div class="modal-body">
                    <p class="zn-modal-lede" style="margin-bottom:10px">
                        We will stop asking you for these documents and close this process. Your profile and
                        everything you have already sent stay on file, and you can apply again later.
                    </p>
                    <div class="zn-fld">
                        <label for="withdraw-note">Anything you would like us to know <span class="zn-opt">optional</span></label>
                        <input type="text" name="note" id="withdraw-note" maxlength="500">
                    </div>
                    <label class="zn-check">
                        <input type="checkbox" name="confirm" value="1" required>
                        <span>Yes, I want to withdraw.</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="zn-btn zn-btn-out zn-btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="zn-btn zn-btn-sm zn-btn-warn">Withdraw</button>
                </div>
            </form>
        </div>
    </div>
@endif

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
