@extends('layouts.form-section')

@section('title', 'Education')

@section('section')

<p class="zn-help" style="margin-top:0">Start with your highest level of education. Add each level you completed.</p>

{{-- Saved entries. Cards rather than a table: the same layout reads on a
     phone without horizontal scrolling, and each entry can carry its own
     actions. --}}
<div id="education-list">
    @forelse ($education as $list)
        <div class="zn-entry">
            <div class="zn-entry-head">
                <div>
                    <p class="zn-entry-title">{{ $list->educ_school }}</p>
                    <p class="zn-entry-sub">{{ $list->educ_level . ($list->educ_degreetitle ? ' · ' . $list->educ_degreetitle : '') ?: '—' }}</p>
                </div>
                <div class="zn-entry-actions">
                    <button type="button" class="zn-link" style="font-size:12px"
                            data-eduid="{{ $list->educ_id }}"
                            data-level="{{ $list->educ_level }}"
                            data-degree="{{ $list->educ_degreetitle }}"
                            data-major="{{ $list->educ_major }}"
                            data-school="{{ $list->educ_school }}"
                            data-address="{{ $list->educ_schooladd }}"
                            data-yeargrad="{{ $list->educ_yeargrad }}"
                            data-curstat="{{ $list->educ_currStatus }}"
                            onclick="edit_education(this)">Edit</button>
                    <button type="button" class="zn-link" style="font-size:12px;color:var(--zn-warn)"
                            data-eduid="{{ $list->educ_id }}"
                            onclick="remove_education(this)">Remove</button>
                </div>
            </div>
                <div class="zn-entry-facts">
                    <div>
                        <span class="zn-fact-label">Status</span>
                        <span class="zn-fact-value @if(!$list->educ_currStatus) empty @endif">{{ $list->educ_currStatus ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Year graduated</span>
                        <span class="zn-fact-value @if(!$list->educ_yeargrad) empty @endif">{{ $list->educ_yeargrad ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Major</span>
                        <span class="zn-fact-value @if(!$list->educ_major) empty @endif">{{ $list->educ_major ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">School address</span>
                        <span class="zn-fact-value @if(!$list->educ_schooladd) empty @endif">{{ $list->educ_schooladd ?: 'Not provided' }}</span>
                    </div>
                </div>
        </div>
    @empty
        <div class="zn-empty">
            <b>No schools added yet.</b>
            Use the button below to add your first one.
        </div>
    @endforelse

    <div class="zn-addbar" style="margin-top:14px">
        <div>
            <h3>Add another school</h3>
            <p>You can add as many as you need.</p>
        </div>
        <button type="button" class="zn-btn zn-btn-out zn-btn-sm" onclick="add_education()">
            <i class="bi bi-plus-lg"></i> Add school
        </button>
    </div>
</div>

{{-- Add / edit form. Hidden until the applicant chooses to add or edit, so
     the page opens on what they have already saved. --}}
<div id="form-education-wrap" class="zn-card d-none">
    <div class="zn-section"><h5 id="form-education-heading">Add school</h5></div>

    <form id="form-education" data-unsaved-guard method="POST" action="{{ route('education.store') }}">
        @csrf
        <input type="hidden" name="education-id" id="education-id">

        <div class="zn-grid">
            <div class="zn-fld zn-col-4">
                <label for="education-level">Level <span class="zn-req">*</span></label>
                <select name="education-level" id="education-level" required>
                    <option value="">Select</option>
                    <option value="Primary">Primary</option>
                    <option value="Secondary">Secondary</option>
                    <option value="Tertiary">Tertiary</option>
                </select>
            </div>
            <div class="zn-fld zn-col-4">
                <label for="education-curstat">Status <span class="zn-req">*</span></label>
                <select name="education-curstat" id="education-curstat" required>
                    <option value="">Select</option>
                    <option value="Completed">Completed</option>
                    <option value="Graduated">Graduated</option>
                    <option value="Currently enrolled">Currently enrolled</option>
                </select>
            </div>
            <div class="zn-fld zn-col-4">
                <label for="education-school">School <span class="zn-req">*</span></label>
                <input type="text" name="education-school" id="education-school" required maxlength="50">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="education-degree">Degree / title <span class="zn-opt">optional</span></label>
                <input type="text" name="education-degree" id="education-degree" placeholder="For tertiary education" maxlength="50">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="education-major">Major <span class="zn-opt">optional</span></label>
                <input type="text" name="education-major" id="education-major" maxlength="50">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="education-year-graduated">Year graduated <span class="zn-opt">optional</span></label>
                <input type="number" name="education-year-graduated" id="education-year-graduated" placeholder="e.g. 2020">
            </div>
            <div class="zn-fld zn-col-12">
                <label for="education-address">School address <span class="zn-opt">optional</span></label>
                <input type="text" name="education-address" id="education-address" maxlength="50">
            </div>
        </div>

        <div class="zn-formnav">
            <span class="zn-formnav-hint"><span class="zn-req">*</span> Required</span>
            <div class="d-flex gap-2">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="btn-cancel-edit-education">Cancel</button>
                <button type="submit" class="zn-btn zn-btn-sm">Save school</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function show_education_form(heading) {
        document.getElementById('form-education-heading').textContent = heading;
        document.getElementById('form-education-wrap').classList.remove('d-none');
        document.getElementById('education-list').classList.add('d-none');
        document.getElementById('form-education-wrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function add_education() {
        document.querySelectorAll('#form-education input, #form-education select').forEach(function (el) {
            if (el.type !== 'hidden' || el.id === 'education-id') el.value = '';
        });
        sync_education_rules();
        show_education_form('Add school');
    }

    // Mirrors EducationController::store: a degree title only for tertiary
    // education, a graduation year unless the applicant is still enrolled.
    function sync_education_rules() {
        const level = document.getElementById('education-level').value;
        const status = document.getElementById('education-curstat').value;
        set_education_required('education-degree', level === 'Tertiary');
        set_education_required('education-year-graduated', status !== 'Currently enrolled');
        const year = document.getElementById('education-year-graduated');
        year.disabled = status === 'Currently enrolled';
        if (year.disabled) year.value = '';
    }

    function set_education_required(id, required) {
        const input = document.getElementById(id);
        input.required = required;
        const marker = document.querySelector('label[for="' + id + '"] .zn-req, label[for="' + id + '"] .zn-opt');
        if (marker) {
            marker.className = required ? 'zn-req' : 'zn-opt';
            marker.textContent = required ? '*' : (id === 'education-year-graduated' ? 'not applicable' : 'optional');
        }
    }

    ['education-level', 'education-curstat'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', sync_education_rules);
    });

    function edit_education(e) {
        document.getElementById('education-id') && (document.getElementById('education-id').value = e.dataset.eduid || '');
        document.getElementById('education-level') && (document.getElementById('education-level').value = e.dataset.level || '');
        document.getElementById('education-degree') && (document.getElementById('education-degree').value = e.dataset.degree || '');
        document.getElementById('education-major') && (document.getElementById('education-major').value = e.dataset.major || '');
        document.getElementById('education-school') && (document.getElementById('education-school').value = e.dataset.school || '');
        document.getElementById('education-address') && (document.getElementById('education-address').value = e.dataset.address || '');
        document.getElementById('education-year-graduated') && (document.getElementById('education-year-graduated').value = e.dataset.yeargrad || '');
        document.getElementById('education-curstat') && (document.getElementById('education-curstat').value = e.dataset.curstat || '');
        sync_education_rules();
        show_education_form('Edit school');
    }

    async function remove_education(e) {
        if (!confirm('Remove this school? This cannot be undone.')) return;

        try {
            const url = @json(route('education.delete', ['id' => ':id'])).replace(':id', e.dataset.eduid);
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

    document.getElementById('btn-cancel-edit-education').addEventListener('click', function () {
        document.getElementById('form-education-wrap').classList.add('d-none');
        document.getElementById('education-list').classList.remove('d-none');
    });
</script>
@endpush
