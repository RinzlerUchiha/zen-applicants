@extends('layouts.form-section')

@section('title', 'Character references')

@section('section')

<p class="zn-help" style="margin-top:0">People who can speak for you — not relatives. Leave this empty if you would rather not name anyone yet.</p>

{{-- Saved entries. Cards rather than a table: the same layout reads on a
     phone without horizontal scrolling, and each entry can carry its own
     actions. --}}
<div id="characterref-list">
    @forelse ($characterref as $list)
        <div class="zn-entry">
            <div class="zn-entry-head">
                <div>
                    <p class="zn-entry-title">{{ $list->ref_fullname }}</p>
                    <p class="zn-entry-sub">{{ $list->ref_position . ($list->ref_company ? ' · ' . $list->ref_company : '') ?: '—' }}</p>
                </div>
                <div class="zn-entry-actions">
                    <button type="button" class="zn-link" style="font-size:12px"
                            data-characterrefid="{{ $list->ref_id }}"
                            data-fullname="{{ $list->ref_fullname }}"
                            data-company="{{ $list->ref_company }}"
                            data-address="{{ $list->ref_address }}"
                            data-position="{{ $list->ref_position }}"
                            data-contact="{{ $list->ref_contact }}"
                            data-relationship="{{ $list->ref_relationship }}"
                            onclick="edit_characterref(this)">Edit</button>
                    <button type="button" class="zn-link" style="font-size:12px;color:var(--zn-warn)"
                            data-characterrefid="{{ $list->ref_id }}"
                            onclick="remove_characterref(this)">Remove</button>
                </div>
            </div>
                <div class="zn-entry-facts">
                    <div>
                        <span class="zn-fact-label">Contact</span>
                        <span class="zn-fact-value @if(!$list->ref_contact) empty @endif">{{ $list->ref_contact ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Relationship</span>
                        <span class="zn-fact-value @if(!$list->ref_relationship) empty @endif">{{ $list->ref_relationship ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="zn-fact-label">Address</span>
                        <span class="zn-fact-value @if(!$list->ref_address) empty @endif">{{ $list->ref_address ?: 'Not provided' }}</span>
                    </div>
                </div>
        </div>
    @empty
        <div class="zn-empty">
            <b>No references added.</b>
            Use the button below to add your first one.
        </div>
    @endforelse

    <div class="zn-addbar" style="margin-top:14px">
        <div>
            <h3>Add another reference</h3>
            <p>You can add as many as you need.</p>
        </div>
        <button type="button" class="zn-btn zn-btn-out zn-btn-sm" onclick="add_characterref()">
            <i class="bi bi-plus-lg"></i> Add reference
        </button>
    </div>
</div>

{{-- Add / edit form. Hidden until the applicant chooses to add or edit, so
     the page opens on what they have already saved. --}}
<div id="form-characterref-wrap" class="zn-card d-none">
    <div class="zn-section"><h5 id="form-characterref-heading">Add reference</h5></div>

    <form id="form-characterref" data-unsaved-guard method="POST" action="{{ route('characterref.store') }}">
        @csrf
        <input type="hidden" name="characterref-id" id="characterref-id">

        <div class="zn-grid">
            <div class="zn-fld zn-col-6">
                <label for="characterref-name">Full name <span class="zn-req">*</span></label>
                <input type="text" name="characterref-name" id="characterref-name" required maxlength="20">
            </div>
            <div class="zn-fld zn-col-3">
                <label for="characterref-contact">Contact number <span class="zn-req">*</span></label>
                <input type="text" name="characterref-contact" id="characterref-contact" required maxlength="11">
            </div>
            <div class="zn-fld zn-col-3">
                <label for="characterref-relationship">Relationship <span class="zn-req">*</span></label>
                <input type="text" name="characterref-relationship" id="characterref-relationship" required maxlength="20">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="characterref-position">Position <span class="zn-opt">optional</span></label>
                <input type="text" name="characterref-position" id="characterref-position" maxlength="50">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="characterref-company">Company <span class="zn-opt">optional</span></label>
                <input type="text" name="characterref-company" id="characterref-company" maxlength="50">
            </div>
            <div class="zn-fld zn-col-4">
                <label for="characterref-address">Address <span class="zn-req">*</span></label>
                <input type="text" name="characterref-address" id="characterref-address" required>
            </div>
        </div>

        <div class="zn-formnav">
            <span class="zn-formnav-hint"><span class="zn-req">*</span> Required</span>
            <div class="d-flex gap-2">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="btn-cancel-edit-characterref">Cancel</button>
                <button type="submit" class="zn-btn zn-btn-sm">Save reference</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function show_characterref_form(heading) {
        document.getElementById('form-characterref-heading').textContent = heading;
        document.getElementById('form-characterref-wrap').classList.remove('d-none');
        document.getElementById('characterref-list').classList.add('d-none');
        document.getElementById('form-characterref-wrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function add_characterref() {
        document.querySelectorAll('#form-characterref input, #form-characterref select').forEach(function (el) {
            if (el.type !== 'hidden' || el.id === 'characterref-id') el.value = '';
        });
        show_characterref_form('Add reference');
    }

    function edit_characterref(e) {
        document.getElementById('characterref-id') && (document.getElementById('characterref-id').value = e.dataset.characterrefid || '');
        document.getElementById('characterref-name') && (document.getElementById('characterref-name').value = e.dataset.fullname || '');
        document.getElementById('characterref-company') && (document.getElementById('characterref-company').value = e.dataset.company || '');
        document.getElementById('characterref-address') && (document.getElementById('characterref-address').value = e.dataset.address || '');
        document.getElementById('characterref-position') && (document.getElementById('characterref-position').value = e.dataset.position || '');
        document.getElementById('characterref-contact') && (document.getElementById('characterref-contact').value = e.dataset.contact || '');
        document.getElementById('characterref-relationship') && (document.getElementById('characterref-relationship').value = e.dataset.relationship || '');
        show_characterref_form('Edit reference');
    }

    async function remove_characterref(e) {
        if (!confirm('Remove this reference? This cannot be undone.')) return;

        try {
            const url = @json(route('characterref.delete', ['id' => ':id'])).replace(':id', e.dataset.characterrefid);
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

    document.getElementById('btn-cancel-edit-characterref').addEventListener('click', function () {
        document.getElementById('form-characterref-wrap').classList.add('d-none');
        document.getElementById('characterref-list').classList.remove('d-none');
    });
</script>
@endpush
