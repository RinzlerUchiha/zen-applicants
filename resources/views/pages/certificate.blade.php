@extends('layouts.form-section')

@section('title', 'Certificates / Trainings')

@section('section')

<p class="zn-help" style="margin-top:0">Trainings and seminars you have completed. Leave this empty if you have none yet.</p>

{{-- Saved entries. Cards rather than a table: the same layout reads on a
     phone without horizontal scrolling, and each entry can carry its own
     actions. --}}
<div id="certificate-list">
    @forelse ($certificate as $list)
        <div class="zn-entry">
            <div class="zn-entry-head">
                <div>
                    <p class="zn-entry-title">{{ $list->cert_title }}</p>
                    <p class="zn-entry-sub">{{ $list->cert_address ?: '—' }}</p>
                </div>
                <div class="zn-entry-actions">
                    <button type="button" class="zn-link" style="font-size:12px"
                            data-certid="{{ $list->cert_id }}"
                            data-title="{{ $list->cert_title }}"
                            data-location="{{ $list->cert_address }}"
                            data-completiondate="{{ $list->cert_date }}"
                            data-speaker="{{ $list->cert_speaker }}"
                            data-attachment="{{ $list->cert_file }}"
                            onclick="edit_certificate(this)">Edit</button>
                    <button type="button" class="zn-link" style="font-size:12px;color:var(--zn-warn)"
                            data-certid="{{ $list->cert_id }}"
                            onclick="remove_certificate(this)">Remove</button>
                </div>
            </div>
                <div class="zn-entry-facts">
                    <div>
                        <span class="zn-fact-label">Completed</span>
                        <span class="zn-fact-value @if(!$list->cert_date) empty @endif">{{ $list->cert_date ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Speaker</span>
                        <span class="zn-fact-value @if(!$list->cert_speaker) empty @endif">{{ $list->cert_speaker ?: 'Not provided' }}</span>
                    </div>
                    @if ($list->cert_file)
                        <div>
                            <span class="zn-fact-label">Attachment</span>
                            <a class="zn-link" style="font-size:13px" target="_blank" rel="noopener"
                               href="{{ url('/file/certificate/' . $list->cert_file) }}">View file</a>
                        </div>
                    @endif
                </div>
        </div>
    @empty
        <div class="zn-empty">
            <b>No certificates or trainings added.</b>
            Use the button below to add your first one.
        </div>
    @endforelse

    <div class="zn-addbar" style="margin-top:14px">
        <div>
            <h3>Add another certificate</h3>
            <p>You can add as many as you need.</p>
        </div>
        <button type="button" class="zn-btn zn-btn-out zn-btn-sm" onclick="add_certificate()">
            <i class="bi bi-plus-lg"></i> Add certificate
        </button>
    </div>
</div>

{{-- Add / edit form. Hidden until the applicant chooses to add or edit, so
     the page opens on what they have already saved. --}}
<div id="form-certificate-wrap" class="zn-card d-none">
    <div class="zn-section"><h5 id="form-certificate-heading">Add certificate</h5></div>

    <form id="form-certificate" method="POST" action="{{ route('certificate.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="certificate-id" id="certificate-id">
            <input type="hidden" name="certificate-attachment-current" id="certificate-attachment-current">

        <div class="zn-grid">
            <div class="zn-fld zn-col-6">
                <label for="certificate-title">Title <span class="zn-req">*</span></label>
                <input type="text" name="certificate-title" id="certificate-title">
            </div>
            <div class="zn-fld zn-col-3">
                <label for="certificate-completion-date">Completion date <span class="zn-req">*</span></label>
                <input type="date" name="certificate-completion-date" id="certificate-completion-date">
            </div>
            <div class="zn-fld zn-col-3">
                <label for="certificate-location">Location <span class="zn-opt">optional</span></label>
                <input type="text" name="certificate-location" id="certificate-location">
            </div>
            <div class="zn-fld zn-col-6">
                <label for="certificate-speaker">Speaker <span class="zn-opt">optional</span></label>
                <input type="text" name="certificate-speaker" id="certificate-speaker">
            </div>
            <div class="zn-fld zn-col-6">
                <label for="certificate-attachment">Attachment <span class="zn-opt">optional</span></label>
                <input type="file" name="certificate-attachment" id="certificate-attachment" accept=".pdf,.jpg,.jpeg,.png">
            </div>
        </div>

        <div class="zn-formnav">
            <span class="zn-formnav-hint"><span class="zn-req">*</span> Required</span>
            <div class="d-flex gap-2">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="btn-cancel-edit-certificate">Cancel</button>
                <button type="submit" class="zn-btn zn-btn-sm">Save certificate</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function show_certificate_form(heading) {
        document.getElementById('form-certificate-heading').textContent = heading;
        document.getElementById('form-certificate-wrap').classList.remove('d-none');
        document.getElementById('certificate-list').classList.add('d-none');
        document.getElementById('form-certificate-wrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function add_certificate() {
        document.querySelectorAll('#form-certificate input, #form-certificate select').forEach(function (el) {
            if (el.type !== 'hidden' || el.id === 'certificate-id') el.value = '';
        });
        show_certificate_form('Add certificate');
    }

    function edit_certificate(e) {
        document.getElementById('certificate-id') && (document.getElementById('certificate-id').value = e.dataset.certid || '');
        document.getElementById('title') && (document.getElementById('title').value = e.dataset.title || '');
        document.getElementById('location') && (document.getElementById('location').value = e.dataset.location || '');
        document.getElementById('completiondate') && (document.getElementById('completiondate').value = e.dataset.completiondate || '');
        document.getElementById('speaker') && (document.getElementById('speaker').value = e.dataset.speaker || '');
        document.getElementById('attachment') && (document.getElementById('attachment').value = e.dataset.attachment || '');
        show_certificate_form('Edit certificate');
    }

    async function remove_certificate(e) {
        if (!confirm('Remove this certificate? This cannot be undone.')) return;

        try {
            const url = @json(route('certificate.delete', ['id' => ':id'])).replace(':id', e.dataset.certid);
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            });
            const data = await response.json();

            if (data.success) {
                location.reload();
            } else {
                alert('Could not remove that entry: ' + (data.error || 'unknown error'));
            }
        } catch (error) {
            console.error(error);
            alert('Could not remove that entry. Please try again.');
        }
    }

    document.getElementById('btn-cancel-edit-certificate').addEventListener('click', function () {
        document.getElementById('form-certificate-wrap').classList.add('d-none');
        document.getElementById('certificate-list').classList.remove('d-none');
    });
</script>
@endpush
