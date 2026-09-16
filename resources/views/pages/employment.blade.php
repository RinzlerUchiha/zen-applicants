@extends('layouts.form-section')

@section('title', 'Employment record')

@section('section')

@php $firstJob = (bool) auth()->user()->app_no_work_experience; @endphp

<p class="zn-help" style="margin-top:0">
    Where you have worked, including a job you have now.
    @if ($employment->isEmpty())
        If you have not worked before, say so below — nothing else is needed here.
    @endif
</p>

{{-- A first-time job seeker has nothing to list. Saying so completes the
     section (skip_flag in config/application_form.php). Only offered while no
     job is recorded; adding a job clears it. --}}
@if ($employment->isEmpty())
    <form method="POST" action="{{ route('employment.first-job') }}" class="zn-card zn-firstjob" id="form-first-job">
        @csrf
        <input type="hidden" name="first-job" value="0">
        <label class="zn-check zn-check-lg" for="first-job">
            <input type="checkbox" name="first-job" id="first-job" value="1" @checked($firstJob)
                   onchange="this.form.submit()">
            <span>
                <b>This is my first job</b> — I have no previous work experience.
                @if ($firstJob)
                    <span class="zn-firstjob-note">Your employment record is complete. Untick this if you want to add a job.</span>
                @endif
            </span>
        </label>
    </form>
@endif

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
                            data-current="{{ $list->empl_is_current ? 1 : 0 }}"
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
                        @if ($list->empl_is_current)
                            <span class="zn-fact-value">Present</span>
                        @else
                            <span class="zn-fact-value @if(!$list->empl_to) empty @endif">{{ $list->empl_to ?: 'Not provided' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="zn-fact-label">Supervisor</span>
                        <span class="zn-fact-value @if(!$list->empl_supervisor) empty @endif">{{ $list->empl_supervisor ?: 'Not provided' }}</span>
                    </div>
                    @unless ($list->empl_is_current)
                        <div>
                            <span class="zn-fact-label">Reason for leaving</span>
                            <span class="zn-fact-value @if(!$list->empl_reason) empty @endif">{{ $list->empl_reason ?: 'Not provided' }}</span>
                        </div>
                    @endunless
                </div>
        </div>
    @empty
        <div class="zn-empty">
            <b>No jobs added yet.</b>
            Add one below, or tick "This is my first job" above.
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

    <form id="form-employment" data-unsaved-guard method="POST" action="{{ route('employment.store') }}">
        @csrf
        <input type="hidden" name="employment-id" id="employment-id">

        <div class="zn-grid">
            <div class="zn-fld zn-col-6">
                <label for="employment-company">Company <span class="zn-req">*</span></label>
                <input type="text" name="employment-company" id="employment-company" required maxlength="50">
            </div>
            <div class="zn-fld zn-col-6">
                <label for="employment-position">Position <span class="zn-req">*</span></label>
                <input type="text" name="employment-position" id="employment-position" required maxlength="20">
            </div>
            <div class="zn-col-full">
                <label class="zn-check" for="employment-current">
                    <input type="checkbox" name="employment-current" id="employment-current" value="1">
                    <span>I currently work here</span>
                </label>
            </div>
            <div class="zn-fld zn-col-4">
                <label for="employment-start-date">Date from <span class="zn-req">*</span></label>
                <input type="date" name="employment-start-date" id="employment-start-date" required>
            </div>
            <div class="zn-fld zn-col-4" data-when-not-current>
                <label for="employment-end-date">Date to <span class="zn-req">*</span></label>
                <input type="date" name="employment-end-date" id="employment-end-date" required>
            </div>
            <div class="zn-fld zn-col-4" data-when-not-current>
                <label for="employment-reason">Reason for leaving <span class="zn-req">*</span></label>
                <input type="text" name="employment-reason" id="employment-reason" required maxlength="100">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="employment-supervisor">Supervisor <span class="zn-opt">optional</span></label>
                <input type="text" name="employment-supervisor" id="employment-supervisor" maxlength="20">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="employment-contact">Supervisor contact <span class="zn-opt">optional</span></label>
                <input type="text" name="employment-contact" id="employment-contact" maxlength="20">
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
            if (el.type === 'checkbox') { el.checked = false; return; }
            if (el.type !== 'hidden' || el.id === 'employment-id') el.value = '';
        });
        sync_employment_current();
        show_employment_form('Add job');
    }

    // A job the applicant still holds has no end date or reason for leaving:
    // those fields are hidden, not required, and not sent.
    function sync_employment_current() {
        const current = document.getElementById('employment-current').checked;
        document.querySelectorAll('#form-employment [data-when-not-current]').forEach(function (wrap) {
            wrap.hidden = current;
            wrap.querySelectorAll('input').forEach(function (input) {
                input.required = !current;
                input.disabled = current;
            });
        });
    }
    document.getElementById('employment-current').addEventListener('change', sync_employment_current);

    function edit_employment(e) {
        document.getElementById('employment-id') && (document.getElementById('employment-id').value = e.dataset.employmentid || '');
        document.getElementById('employment-company') && (document.getElementById('employment-company').value = e.dataset.company || '');
        document.getElementById('employment-address') && (document.getElementById('employment-address').value = e.dataset.address || '');
        document.getElementById('employment-position') && (document.getElementById('employment-position').value = e.dataset.position || '');
        document.getElementById('employment-supervisor') && (document.getElementById('employment-supervisor').value = e.dataset.supervisor || '');
        document.getElementById('employment-contact') && (document.getElementById('employment-contact').value = e.dataset.contact || '');
        document.getElementById('employment-start-date') && (document.getElementById('employment-start-date').value = e.dataset.from || '');
        document.getElementById('employment-end-date') && (document.getElementById('employment-end-date').value = e.dataset.to || '');
        document.getElementById('employment-reason') && (document.getElementById('employment-reason').value = e.dataset.reason || '');
        document.getElementById('employment-current').checked = e.dataset.current === '1';
        sync_employment_current();
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
