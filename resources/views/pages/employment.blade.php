@extends('layouts.form-section')

@section('title', 'Employment record')

@section('section')

<p class="zn-help" style="margin-top:0">Where you have worked before. If this is your first job, tick the box below instead.</p>

{{-- Saved entries. Cards rather than a table: the same layout reads on a
     phone without horizontal scrolling, and each entry can carry its own
     actions. --}}
<div id="employment-list">
    @forelse ($employment as $list)
        <div class="zn-entry">
            <div class="zn-entry-head">
                <div>
                    <p class="zn-entry-title">{{ $list->empl_company }}</p>
                    <p class="zn-entry-sub">{{ $list->empl_position ?: '—' }}</p>
                </div>
                <div class="zn-entry-actions">
                    <button type="button" class="zn-link" style="font-size:12px"
                            data-employmentid="{{ $list->empl_id }}"
                            data-company="{{ $list->empl_company }}"
                            data-address="{{ $list->empl_address }}"
                            data-position="{{ $list->empl_position }}"
                            data-supervisor="{{ $list->empl_supervisor }}"
                            data-contact="{{ $list->empl_contact }}"
                            data-from="{{ $list->empl_from }}"
                            data-to="{{ $list->empl_to }}"
                            data-reason="{{ $list->empl_reason }}"
                            onclick="edit_employment(this)">Edit</button>
                    <button type="button" class="zn-link" style="font-size:12px;color:var(--zn-warn)"
                            data-employmentid="{{ $list->empl_id }}"
                            onclick="remove_employment(this)">Remove</button>
                </div>
            </div>
                <div class="zn-entry-facts">
                    <div>
                        <span class="zn-fact-label">From</span>
                        <span class="zn-fact-value @if(!$list->empl_from) empty @endif">{{ $list->empl_from ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">To</span>
                        <span class="zn-fact-value @if(!$list->empl_to) empty @endif">{{ $list->empl_to ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Supervisor</span>
                        <span class="zn-fact-value @if(!$list->empl_supervisor) empty @endif">{{ $list->empl_supervisor ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Reason for leaving</span>
                        <span class="zn-fact-value @if(!$list->empl_reason) empty @endif">{{ $list->empl_reason ?: 'Not provided' }}</span>
                    </div>
                </div>
        </div>
    @empty
        <div class="zn-empty">
            <b>No previous jobs added yet.</b>
            Use the button below to add your first one.
        </div>
    @endforelse

    <div class="zn-addbar" style="margin-top:14px">
        <div>
            <h3>Add another job</h3>
            <p>You can add as many as you need.</p>
        </div>
        <button type="button" class="zn-btn zn-btn-out zn-btn-sm" onclick="add_employment()">
            <i class="bi bi-plus-lg"></i> Add job
        </button>
    </div>
</div>

{{-- Add / edit form. Hidden until the applicant chooses to add or edit, so
     the page opens on what they have already saved. --}}
<div id="form-employment-wrap" class="zn-card d-none">
    <div class="zn-section"><h5 id="form-employment-heading">Add job</h5></div>

    <form id="form-employment" method="POST" action="{{ route('employment.store') }}">
        @csrf
        <input type="hidden" name="employment-id" id="employment-id">

        <div class="zn-grid">
            <div class="zn-fld zn-col-6">
                <label for="employment-company">Company <span class="zn-req">*</span></label>
                <input type="text" name="employment-company" id="employment-company">
            </div>
            <div class="zn-fld zn-col-6">
                <label for="employment-position">Position <span class="zn-req">*</span></label>
                <input type="text" name="employment-position" id="employment-position">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="employment-start-date">Date from <span class="zn-req">*</span></label>
                <input type="date" name="employment-start-date" id="employment-start-date">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="employment-end-date">Date to <span class="zn-opt">optional</span></label>
                <input type="date" name="employment-end-date" id="employment-end-date" placeholder="Leave blank if you still work here">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="employment-reason">Reason for leaving <span class="zn-opt">optional</span></label>
                <input type="text" name="employment-reason" id="employment-reason">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="employment-supervisor">Supervisor <span class="zn-opt">optional</span></label>
                <input type="text" name="employment-supervisor" id="employment-supervisor">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="employment-contact">Supervisor contact <span class="zn-opt">optional</span></label>
                <input type="text" name="employment-contact" id="employment-contact">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="employment-address">Company address <span class="zn-opt">optional</span></label>
                <input type="text" name="employment-address" id="employment-address">
            </div>
        </div>

        <div class="zn-formnav">
            <span class="zn-formnav-hint"><span class="zn-req">*</span> Required</span>
            <div class="d-flex gap-2">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="btn-cancel-edit-employment">Cancel</button>
                <button type="submit" class="zn-btn zn-btn-sm">Save job</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function show_employment_form(heading) {
        document.getElementById('form-employment-heading').textContent = heading;
        document.getElementById('form-employment-wrap').classList.remove('d-none');
        document.getElementById('employment-list').classList.add('d-none');
        document.getElementById('form-employment-wrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function add_employment() {
        document.querySelectorAll('#form-employment input, #form-employment select').forEach(function (el) {
            if (el.type !== 'hidden' || el.id === 'employment-id') el.value = '';
        });
        show_employment_form('Add job');
    }

    function edit_employment(e) {
        document.getElementById('employment-id') && (document.getElementById('employment-id').value = e.dataset.employmentid || '');
        document.getElementById('company') && (document.getElementById('company').value = e.dataset.company || '');
        document.getElementById('address') && (document.getElementById('address').value = e.dataset.address || '');
        document.getElementById('position') && (document.getElementById('position').value = e.dataset.position || '');
        document.getElementById('supervisor') && (document.getElementById('supervisor').value = e.dataset.supervisor || '');
        document.getElementById('contact') && (document.getElementById('contact').value = e.dataset.contact || '');
        document.getElementById('from') && (document.getElementById('from').value = e.dataset.from || '');
        document.getElementById('to') && (document.getElementById('to').value = e.dataset.to || '');
        document.getElementById('reason') && (document.getElementById('reason').value = e.dataset.reason || '');
        show_employment_form('Edit job');
    }

    async function remove_employment(e) {
        if (!confirm('Remove this job? This cannot be undone.')) return;

        try {
            const url = @json(route('employment.delete', ['id' => ':id'])).replace(':id', e.dataset.employmentid);
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

    document.getElementById('btn-cancel-edit-employment').addEventListener('click', function () {
        document.getElementById('form-employment-wrap').classList.add('d-none');
        document.getElementById('employment-list').classList.remove('d-none');
    });
</script>
@endpush
