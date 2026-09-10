@extends('layouts.form-section')

@section('title', 'Family background')

@section('section')

<p class="zn-help" style="margin-top:0">Your parents, spouse, children and siblings. Add at least one — and if you are married, include your spouse.</p>

{{-- Saved entries. Cards rather than a table: the same layout reads on a
     phone without horizontal scrolling, and each entry can carry its own
     actions. --}}
<div id="family-list">
    @forelse ($family as $list)
        <div class="zn-entry">
            <div class="zn-entry-head">
                <div>
                    <p class="zn-entry-title">{{ $list->fam_firstname . ' ' . $list->fam_lastname }}</p>
                    <p class="zn-entry-sub">{{ $list->fam_relationship ?: '—' }}</p>
                </div>
                <div class="zn-entry-actions">
                    <button type="button" class="zn-link" style="font-size:12px"
                            data-famid="{{ $list->fam_id }}"
                            data-relationship="{{ $list->fam_relationship }}"
                            data-firstname="{{ $list->fam_firstname }}"
                            data-middlename="{{ $list->fam_midname }}"
                            data-lastname="{{ $list->fam_lastname }}"
                            data-suffix="{{ $list->fam_suffix }}"
                            data-maidenname="{{ $list->fam_maidenname }}"
                            data-birthdate="{{ $list->fam_birthdate }}"
                            data-sex="{{ $list->fam_sex }}"
                            data-contact="{{ $list->fam_contact }}"
                            data-address="{{ $list->fam_add }}"
                            data-occupation="{{ $list->fam_occupation }}"
                            data-workplace="{{ $list->fam_workplace }}"
                            onclick="edit_family(this)">Edit</button>
                    <button type="button" class="zn-link" style="font-size:12px;color:var(--zn-warn)"
                            data-famid="{{ $list->fam_id }}"
                            onclick="remove_family(this)">Remove</button>
                </div>
            </div>
                <div class="zn-entry-facts">
                    <div>
                        <span class="zn-fact-label">Sex</span>
                        <span class="zn-fact-value @if(!$list->fam_sex) empty @endif">{{ $list->fam_sex ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Birth date</span>
                        <span class="zn-fact-value @if(!$list->fam_birthdate) empty @endif">{{ $list->fam_birthdate ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Contact</span>
                        <span class="zn-fact-value @if(!$list->fam_contact) empty @endif">{{ $list->fam_contact ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Occupation</span>
                        <span class="zn-fact-value @if(!$list->fam_occupation) empty @endif">{{ $list->fam_occupation ?: 'Not provided' }}</span>
                    </div>
                </div>
        </div>
    @empty
        <div class="zn-empty">
            <b>No family members added yet.</b>
            Use the button below to add your first one.
        </div>
    @endforelse

    <div class="zn-addbar" style="margin-top:14px">
        <div>
            <h3>Add another family member</h3>
            <p>You can add as many as you need.</p>
        </div>
        <button type="button" class="zn-btn zn-btn-out zn-btn-sm" onclick="add_family()">
            <i class="bi bi-plus-lg"></i> Add family member
        </button>
    </div>
</div>

{{-- Add / edit form. Hidden until the applicant chooses to add or edit, so
     the page opens on what they have already saved. --}}
<div id="form-family-wrap" class="zn-card d-none">
    <div class="zn-section"><h5 id="form-family-heading">Add family member</h5></div>

    <form id="form-family" method="POST" action="{{ route('family.store') }}">
        @csrf
        <input type="hidden" name="family-id" id="family-id">

        <div class="zn-grid">
            <div class="zn-fld zn-col-4">
                <label for="family-relationship">Relationship <span class="zn-req">*</span></label>
                <select name="family-relationship" id="family-relationship">
                    <option value="">Select</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Mother">Mother</option>
                    <option value="Father">Father</option>
                    <option value="Son">Son</option>
                    <option value="Daughter">Daughter</option>
                    <option value="Sister">Sister</option>
                    <option value="Brother">Brother</option>
                    <option value="Live-in Partner">Live-in Partner</option>
                </select>
            </div>
            <div class="zn-fld zn-col-4">
                <label for="family-firstname">First name <span class="zn-req">*</span></label>
                <input type="text" name="family-firstname" id="family-firstname">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="family-middlename">Middle name <span class="zn-opt">optional</span></label>
                <input type="text" name="family-middlename" id="family-middlename">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="family-lastname">Last name <span class="zn-req">*</span></label>
                <input type="text" name="family-lastname" id="family-lastname">
            </div>
            <div class="zn-fld zn-col-2">
                <label for="family-suffix">Suffix <span class="zn-opt">optional</span></label>
                <input type="text" name="family-suffix" id="family-suffix">
            </div>
            <div class="zn-fld zn-col-3">
                <label for="family-maidenname">Maiden name <span class="zn-opt">optional</span></label>
                <input type="text" name="family-maidenname" id="family-maidenname">
            </div>
            <div class="zn-fld zn-col-3">
                <label for="family-sex">Sex <span class="zn-req">*</span></label>
                <select name="family-sex" id="family-sex">
                    <option value="">Select</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="zn-fld zn-col-3">
                <label for="family-birthdate">Birth date <span class="zn-opt">optional</span></label>
                <input type="date" name="family-birthdate" id="family-birthdate">
            </div>
            <div class="zn-fld zn-col-3">
                <label for="family-contact">Contact number <span class="zn-opt">optional</span></label>
                <input type="text" name="family-contact" id="family-contact">
            </div>
            <div class="zn-fld zn-col-3">
                <label for="family-occupation">Occupation <span class="zn-opt">optional</span></label>
                <input type="text" name="family-occupation" id="family-occupation">
            </div>
            <div class="zn-fld zn-col-6">
                <label for="family-workplace">Work address <span class="zn-opt">optional</span></label>
                <input type="text" name="family-workplace" id="family-workplace">
            </div>
            <div class="zn-fld zn-col-6">
                <label for="family-address">Address <span class="zn-opt">optional</span></label>
                <input type="text" name="family-address" id="family-address">
            </div>
        </div>

        <div class="zn-formnav">
            <span class="zn-formnav-hint"><span class="zn-req">*</span> Required</span>
            <div class="d-flex gap-2">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="btn-cancel-edit-family">Cancel</button>
                <button type="submit" class="zn-btn zn-btn-sm">Save family member</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function show_family_form(heading) {
        document.getElementById('form-family-heading').textContent = heading;
        document.getElementById('form-family-wrap').classList.remove('d-none');
        document.getElementById('family-list').classList.add('d-none');
        document.getElementById('form-family-wrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function add_family() {
        document.querySelectorAll('#form-family input, #form-family select').forEach(function (el) {
            if (el.type !== 'hidden' || el.id === 'family-id') el.value = '';
        });
        show_family_form('Add family member');
    }

    function edit_family(e) {
        document.getElementById('family-id') && (document.getElementById('family-id').value = e.dataset.famid || '');
        document.getElementById('relationship') && (document.getElementById('relationship').value = e.dataset.relationship || '');
        document.getElementById('firstname') && (document.getElementById('firstname').value = e.dataset.firstname || '');
        document.getElementById('middlename') && (document.getElementById('middlename').value = e.dataset.middlename || '');
        document.getElementById('lastname') && (document.getElementById('lastname').value = e.dataset.lastname || '');
        document.getElementById('suffix') && (document.getElementById('suffix').value = e.dataset.suffix || '');
        document.getElementById('maidenname') && (document.getElementById('maidenname').value = e.dataset.maidenname || '');
        document.getElementById('birthdate') && (document.getElementById('birthdate').value = e.dataset.birthdate || '');
        document.getElementById('sex') && (document.getElementById('sex').value = e.dataset.sex || '');
        document.getElementById('contact') && (document.getElementById('contact').value = e.dataset.contact || '');
        document.getElementById('address') && (document.getElementById('address').value = e.dataset.address || '');
        document.getElementById('occupation') && (document.getElementById('occupation').value = e.dataset.occupation || '');
        document.getElementById('workplace') && (document.getElementById('workplace').value = e.dataset.workplace || '');
        show_family_form('Edit family member');
    }

    async function remove_family(e) {
        if (!confirm('Remove this family member? This cannot be undone.')) return;

        try {
            const url = @json(route('family.delete', ['id' => ':id'])).replace(':id', e.dataset.famid);
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

    document.getElementById('btn-cancel-edit-family').addEventListener('click', function () {
        document.getElementById('form-family-wrap').classList.add('d-none');
        document.getElementById('family-list').classList.remove('d-none');
    });
</script>
@endpush
