@extends('layouts.layout')

@section('title', 'Documents')

@section('content')

<p class="zn-crumb">Send to HR</p>
<p class="zn-page-title">Documents</p>
<p class="zn-page-sub" style="max-width:62ch">
    {{ $required->count() }} items are required for your application. We accept
    {{ strtoupper(implode(', ', $extensions)) }} up to {{ round($maxSizeKb / 1024) }} MB.
    A photo taken with your phone is fine, as long as the text is readable.
</p>

<form id="form-document" method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="zn-drop" id="dropzone">
        <div class="zn-drop-icon"><i class="bi bi-upload"></i></div>
        <b>Choose a file, or drag one here</b>
        <span>{{ strtoupper(implode(', ', $extensions)) }} · {{ round($maxSizeKb / 1024) }} MB maximum</span>

        <div class="row g-2 justify-content-center mt-3">
            <div class="col-sm-4">
                <select class="form-select form-select-sm" name="doc_type" id="doc_type" required>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(old('doc_type') === $value)>
                            {{ $label }}@if ($required->contains($value)) — required @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 d-none" id="doc-label-wrap">
                <input type="text" class="form-control form-control-sm" name="doc_label" id="doc_label"
                       maxlength="150" value="{{ old('doc_label') }}" placeholder="Name this document">
            </div>
            <div class="col-sm-4">
                <input type="file" class="form-control form-control-sm" name="doc_file" id="doc_file"
                       accept=".{{ implode(',.', $extensions) }}" required>
            </div>
        </div>

        <button type="submit" class="zn-btn zn-btn-sm mt-3" id="btn-upload">Upload</button>
    </div>
</form>

@php
    $byType = $documents->groupBy('doc_type');
    $extras = $documents->reject(fn ($d) => $required->contains($d->doc_type) || $optional->contains($d->doc_type));
@endphp

<p class="zn-group-label">Required</p>
<div class="zn-doc-list">
    @foreach ($required as $type)
        @php $uploaded = $byType->get($type); @endphp

        @if ($uploaded)
            @foreach ($uploaded as $document)
                @include('pages.partials.document-row', ['document' => $document])
            @endforeach
        @else
            <div class="zn-doc required-missing">
                <div class="zn-doc-ico"><i class="bi bi-exclamation-lg"></i></div>
                <div>
                    <div class="zn-doc-name">{{ config('documents.types.' . $type) }}</div>
                    <div class="zn-doc-meta">Still needed to complete your application</div>
                </div>
                <button type="button" class="zn-btn zn-btn-sm js-pick" data-type="{{ $type }}">Upload</button>
            </div>
        @endif
    @endforeach
</div>

<p class="zn-group-label">Optional</p>
<div class="zn-doc-list">
    @foreach ($optional as $type)
        @php $uploaded = $byType->get($type); @endphp

        @if ($uploaded)
            @foreach ($uploaded as $document)
                @include('pages.partials.document-row', ['document' => $document])
            @endforeach
        @else
            <div class="zn-doc missing">
                <div class="zn-doc-ico">—</div>
                <div>
                    <div class="zn-doc-name">{{ config('documents.types.' . $type) }}</div>
                    <div class="zn-doc-meta">Not required, but it helps your application stand out</div>
                </div>
                <button type="button" class="zn-link js-pick" data-type="{{ $type }}">Upload</button>
            </div>
        @endif
    @endforeach
</div>

@if ($extras->isNotEmpty())
    <p class="zn-group-label">Also uploaded</p>
    <div class="zn-doc-list">
        @foreach ($extras as $document)
            @include('pages.partials.document-row', ['document' => $document])
        @endforeach
    </div>
@endif

@endsection

@push('scripts')
<script>
    $(function () {
        const otherType = @json($otherType);

        function toggleLabel() {
            const isOther = $('#doc_type').val() === otherType;
            $('#doc-label-wrap').toggleClass('d-none', !isOther);
            if (!isOther) $('#doc_label').val('');
        }

        $('#doc_type').on('change', toggleLabel);
        toggleLabel();

        // "Upload" beside a missing document preselects that type and opens the
        // file picker, so the applicant never has to find it in the dropdown.
        $('.js-pick').on('click', function () {
            $('#doc_type').val($(this).data('type')).trigger('change');
            $('#doc_file').trigger('click');
        });

        $('#form-document').on('submit', function () {
            $('#btn-upload').prop('disabled', true).text('Uploading…');
        });

        // Drag and drop onto the zone fills the same file input, so there is
        // one upload path rather than two.
        const zone = document.getElementById('dropzone');
        ['dragenter', 'dragover'].forEach(evt => zone.addEventListener(evt, e => {
            e.preventDefault();
            zone.style.borderColor = 'var(--zn-accent)';
        }));
        ['dragleave', 'drop'].forEach(evt => zone.addEventListener(evt, e => {
            e.preventDefault();
            zone.style.borderColor = '';
        }));
        zone.addEventListener('drop', e => {
            if (e.dataTransfer.files.length) {
                document.getElementById('doc_file').files = e.dataTransfer.files;
            }
        });
    });
</script>
@endpush
