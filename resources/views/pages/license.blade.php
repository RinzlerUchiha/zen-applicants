@extends('layouts.form-section')

@section('title', 'Eligibility / Licences')

@section('section')

<p class="zn-help" style="margin-top:0">Professional licences and civil service eligibilities. Leave this empty if you have none.</p>

{{-- Saved entries. Cards rather than a table: the same layout reads on a
     phone without horizontal scrolling, and each entry can carry its own
     actions. --}}
<div id="license-list">
    @forelse ($license as $list)
        <div class="zn-entry">
            <div class="zn-entry-head">
                <div>
                    <p class="zn-entry-title">{{ $list->el_type }}</p>
                    <p class="zn-entry-sub">{{ $list->el_profession ?: '—' }}</p>
                </div>
                <div class="zn-entry-actions">
                    <button type="button" class="zn-link" style="font-size:12px"
                            data-licenseid="{{ $list->el_id }}"
                            data-type="{{ $list->el_type }}"
                            data-profession="{{ $list->el_profession }}"
                            data-registerdate="{{ $list->el_regdate }}"
                            data-validuntil="{{ $list->el_expdate }}"
                            data-attachment="{{ $list->el_file }}"
                            onclick="edit_license(this)">Edit</button>
                    <button type="button" class="zn-link" style="font-size:12px;color:var(--zn-warn)"
                            data-licenseid="{{ $list->el_id }}"
                            onclick="remove_license(this)">Remove</button>
                </div>
            </div>
                <div class="zn-entry-facts">
                    <div>
                        <span class="zn-fact-label">Registered</span>
                        <span class="zn-fact-value @if(!$list->el_regdate) empty @endif">{{ $list->el_regdate ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Valid until</span>
                        <span class="zn-fact-value @if(!$list->el_expdate) empty @endif">{{ $list->el_expdate ?: 'Not provided' }}</span>
                    </div>
                    @if ($list->el_file)
                        <div>
                            <span class="zn-fact-label">Attachment</span>
                            <a class="zn-link" style="font-size:13px" target="_blank" rel="noopener"
                               href="{{ url('/file/license/' . $list->el_file) }}">View file</a>
                        </div>
                    @endif
                </div>
        </div>
    @empty
        <div class="zn-empty">
            <b>No licences or eligibilities added.</b>
            Use the button below to add your first one.
        </div>
    @endforelse

    <div class="zn-addbar" style="margin-top:14px">
        <div>
            <h3>Add another licence</h3>
            <p>You can add as many as you need.</p>
        </div>
        <button type="button" class="zn-btn zn-btn-out zn-btn-sm" onclick="add_license()">
            <i class="bi bi-plus-lg"></i> Add licence
        </button>
    </div>
</div>

{{-- Add / edit form. Hidden until the applicant chooses to add or edit, so
     the page opens on what they have already saved. --}}
<div id="form-license-wrap" class="zn-card d-none">
    <div class="zn-section"><h5 id="form-license-heading">Add licence</h5></div>

    <form id="form-license" method="POST" action="{{ route('license.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="license-id" id="license-id">
            <input type="hidden" name="license-attachment-current" id="license-attachment-current">

        <div class="zn-grid">
            <div class="zn-fld zn-col-6">
                <label for="license-type">Licence type <span class="zn-req">*</span></label>
                <input type="text" name="license-type" id="license-type">
            </div>
            <div class="zn-fld zn-col-6">
                <label for="license-profession">Profession <span class="zn-req">*</span></label>
                <input type="text" name="license-profession" id="license-profession">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="license-registration-date">Registration date <span class="zn-req">*</span></label>
                <input type="date" name="license-registration-date" id="license-registration-date">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="license-valid-until">Valid until <span class="zn-opt">optional</span></label>
                <input type="date" name="license-valid-until" id="license-valid-until" placeholder="Blank if it does not expire">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="license-attachment">Attachment <span class="zn-opt">optional</span></label>
                <input type="file" name="license-attachment" id="license-attachment" accept=".pdf,.jpg,.jpeg,.png">
            </div>
        </div>

        <div class="zn-formnav">
            <span class="zn-formnav-hint"><span class="zn-req">*</span> Required</span>
            <div class="d-flex gap-2">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="btn-cancel-edit-license">Cancel</button>
                <button type="submit" class="zn-btn zn-btn-sm">Save licence</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function show_license_form(heading) {
        document.getElementById('form-license-heading').textContent = heading;
        document.getElementById('form-license-wrap').classList.remove('d-none');
        document.getElementById('license-list').classList.add('d-none');
        document.getElementById('form-license-wrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function add_license() {
        document.querySelectorAll('#form-license input, #form-license select').forEach(function (el) {
            if (el.type !== 'hidden' || el.id === 'license-id') el.value = '';
        });
        show_license_form('Add licence');
    }

    function edit_license(e) {
        document.getElementById('license-id') && (document.getElementById('license-id').value = e.dataset.licenseid || '');
        document.getElementById('type') && (document.getElementById('type').value = e.dataset.type || '');
        document.getElementById('profession') && (document.getElementById('profession').value = e.dataset.profession || '');
        document.getElementById('registerdate') && (document.getElementById('registerdate').value = e.dataset.registerdate || '');
        document.getElementById('validuntil') && (document.getElementById('validuntil').value = e.dataset.validuntil || '');
        document.getElementById('attachment') && (document.getElementById('attachment').value = e.dataset.attachment || '');
        show_license_form('Edit licence');
    }

    async function remove_license(e) {
        if (!confirm('Remove this licence? This cannot be undone.')) return;

        try {
            const url = @json(route('license.delete', ['id' => ':id'])).replace(':id', e.dataset.licenseid);
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

    document.getElementById('btn-cancel-edit-license').addEventListener('click', function () {
        document.getElementById('form-license-wrap').classList.add('d-none');
        document.getElementById('license-list').classList.remove('d-none');
    });
</script>
@endpush
