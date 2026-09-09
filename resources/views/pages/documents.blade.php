@extends('layouts.layout')

@section('content')

<style>
    #documents-panel {
        font-size: 12px;
        min-width: 50vw;
        width: fit-content;
    }

    #documents-panel .doc-hint {
        font-size: 11px;
    }

    #documents-empty {
        border: 1px dashed var(--bs-border-color);
        border-radius: .375rem;
    }
</style>

<script type="text/javascript">
    $(function () {
        // "Other" is the only type the applicant names themselves.
        const otherType = @json($otherType);

        function toggleLabel() {
            const isOther = $('#doc_type').val() === otherType;
            $('#doc-label-wrap').toggleClass('d-none', !isOther);
            if (!isOther) $('#doc_label').val('');
        }

        $('#doc_type').on('change', toggleLabel);
        toggleLabel();

        $('#form-document').on('submit', function () {
            $('#btn-upload').prop('disabled', true).text('Uploading…');
        });

        $('.btn-remove-document').on('click', function (e) {
            if (!confirm('Remove this document? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
</script>

<div id="documents-panel">

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h6 class="text-uppercase text-body-light mb-1">My Documents</h6>
    <p class="doc-hint text-body-secondary mb-3">
        Upload clear copies of your documents.
        Accepted formats: {{ strtoupper(implode(', ', $extensions)) }}.
        Maximum size: {{ round($maxSizeKb / 1024) }} MB per file.
        You may upload more than one file for the same document type.
    </p>

    @if ($documents->isEmpty())
        <div id="documents-empty" class="text-center text-body-secondary p-4 mb-3">
            You have not uploaded any documents yet.<br>
            Use the form below to add your first one.
        </div>
    @else
        <table class="table table-sm table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>File</th>
                    <th>Size</th>
                    <th>Uploaded</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $document)
                    <tr>
                        <td class="text-nowrap">{{ $document->type_label }}</td>
                        <td class="text-break">{{ $document->doc_original_name }}</td>
                        <td class="text-nowrap">{{ $document->size_for_humans }}</td>
                        <td class="text-nowrap">{{ $document->uploaded_at?->format('M d, Y') }}</td>
                        <td>
                            <div class="d-flex">
                                <a class="btn btn-outline-secondary btn-sm m-1"
                                   href="{{ route('documents.view', $document->id) }}"
                                   target="_blank" rel="noopener">View</a>

                                <form method="POST"
                                      action="{{ route('documents.delete', $document->id) }}"
                                      class="m-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-outline-danger btn-sm btn-remove-document">Remove</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <hr class="my-3">

    <form id="form-document" method="POST" action="{{ route('documents.store') }}"
          enctype="multipart/form-data" class="mb-3">
        @csrf

        <div class="row g-3 align-items-end">
            <div class="col-lg-auto">
                <label for="doc_type" class="form-label mb-1">Document Type</label>
                <select class="form-select form-select-sm" name="doc_type" id="doc_type" required>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(old('doc_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-auto d-none" id="doc-label-wrap">
                <label for="doc_label" class="form-label mb-1">Document Name</label>
                <input type="text" class="form-control form-control-sm" name="doc_label"
                       id="doc_label" maxlength="150" value="{{ old('doc_label') }}"
                       placeholder="e.g. Barangay Clearance">
            </div>

            <div class="col-lg-auto">
                <label for="doc_file" class="form-label mb-1">File</label>
                <input type="file" class="form-control form-control-sm" name="doc_file" id="doc_file"
                       accept=".{{ implode(',.', $extensions) }}" required>
            </div>

            <div class="col-lg-auto">
                <button type="submit" class="btn btn-primary btn-sm" id="btn-upload">Upload</button>
            </div>
        </div>
    </form>

</div>

@stop
