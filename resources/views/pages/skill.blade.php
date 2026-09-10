@extends('layouts.form-section')

@section('title', 'Special skills')

@section('section')

<p class="zn-help" style="margin-top:0">Anything you are good at that could help in the role. Pick from the list, or describe your own.</p>

{{-- Saved entries. Cards rather than a table: the same layout reads on a
     phone without horizontal scrolling, and each entry can carry its own
     actions. --}}
<div id="skill-list">
    @forelse ($skill as $list)
        <div class="zn-entry">
            <div class="zn-entry-head">
                <div>
                    <p class="zn-entry-title">{{ $list->sc_title ?: 'Skill' }}</p>
                    <p class="zn-entry-sub">{{ $list->skill_name ?: $list->skill_others ?: '—' }}</p>
                </div>
                <div class="zn-entry-actions">
                    <button type="button" class="zn-link" style="font-size:12px"
                            data-skillid="{{ $list->skill_id }}"
                            data-category="{{ $list->skill_category }}"
                            data-type="{{ $list->skill_type }}"
                            data-other="{{ $list->skill_others }}"
                            onclick="edit_skill(this)">Edit</button>
                    <button type="button" class="zn-link" style="font-size:12px;color:var(--zn-warn)"
                            data-skillid="{{ $list->skill_id }}"
                            onclick="remove_skill(this)">Remove</button>
                </div>
            </div>
        </div>
    @empty
        <div class="zn-empty">
            <b>No skills added yet.</b>
            Use the button below to add your first one.
        </div>
    @endforelse

    <div class="zn-addbar" style="margin-top:14px">
        <div>
            <h3>Add another skill</h3>
            <p>You can add as many as you need.</p>
        </div>
        <button type="button" class="zn-btn zn-btn-out zn-btn-sm" onclick="add_skill()">
            <i class="bi bi-plus-lg"></i> Add skill
        </button>
    </div>
</div>

{{-- Add / edit form. Hidden until the applicant chooses to add or edit, so
     the page opens on what they have already saved. --}}
<div id="form-skill-wrap" class="zn-card d-none">
    <div class="zn-section"><h5 id="form-skill-heading">Add skill</h5></div>

    <form id="form-skill" method="POST" action="{{ route('skill.store') }}">
        @csrf
        <input type="hidden" name="skill-id" id="skill-id">

        <div class="zn-grid">
            <div class="zn-fld zn-col-4">
                <label for="skill-category">Category <span class="zn-req">*</span></label>
                <select name="skill-category" id="skill-category">
                    <option value="">Select a category</option>
                    @foreach ($skillsCategoryList as $sc)
                        <option value="{{ $sc->sc_id }}">{{ $sc->sc_title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="zn-fld zn-col-4">
                <label for="skill-type">Type <span class="zn-opt">optional</span></label>
                <select name="skill-type" id="skill-type">
                    <option value="">Select a skill</option>
                    @foreach ($skillsList as $sl)
                        <option value="{{ $sl->id }}" data-category="{{ $sl->skil_categID }}">{{ $sl->skill_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="zn-fld zn-col-4">
                <label for="skill-other">Or describe your own <span class="zn-opt">optional</span></label>
                <input type="text" name="skill-other" id="skill-other" placeholder="If it is not on the list">
            </div>
        </div>

        <div class="zn-formnav">
            <span class="zn-formnav-hint"><span class="zn-req">*</span> Required</span>
            <div class="d-flex gap-2">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="btn-cancel-edit-skill">Cancel</button>
                <button type="submit" class="zn-btn zn-btn-sm">Save skill</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function show_skill_form(heading) {
        document.getElementById('form-skill-heading').textContent = heading;
        document.getElementById('form-skill-wrap').classList.remove('d-none');
        document.getElementById('skill-list').classList.add('d-none');
        document.getElementById('form-skill-wrap').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function add_skill() {
        document.querySelectorAll('#form-skill input, #form-skill select').forEach(function (el) {
            if (el.type !== 'hidden' || el.id === 'skill-id') el.value = '';
        });
        show_skill_form('Add skill');
    }

    function edit_skill(e) {
        document.getElementById('skill-id') && (document.getElementById('skill-id').value = e.dataset.skillid || '');
        document.getElementById('category') && (document.getElementById('category').value = e.dataset.category || '');
        document.getElementById('type') && (document.getElementById('type').value = e.dataset.type || '');
        document.getElementById('other') && (document.getElementById('other').value = e.dataset.other || '');
        if (window.__filterSkillTypes) window.__filterSkillTypes();
        show_skill_form('Edit skill');
    }

    async function remove_skill(e) {
        if (!confirm('Remove this skill? This cannot be undone.')) return;

        try {
            const url = @json(route('skill.delete', ['id' => ':id'])).replace(':id', e.dataset.skillid);
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

    // Only show skill types belonging to the chosen category — the full list
    // is long, and a type from another category would not make sense.
    (function () {
        const category = document.getElementById('skill-category');
        const type = document.getElementById('skill-type');

        function filterTypes() {
            const chosen = category.value;
            let visibleSelected = false;

            type.querySelectorAll('option[data-category]').forEach(function (option) {
                const match = !chosen || option.dataset.category === chosen;
                option.hidden = !match;
                if (match && option.value === type.value) visibleSelected = true;
            });

            if (!visibleSelected) type.value = '';
        }

        category.addEventListener('change', filterTypes);
        filterTypes();
        window.__filterSkillTypes = filterTypes;
    })();

    document.getElementById('btn-cancel-edit-skill').addEventListener('click', function () {
        document.getElementById('form-skill-wrap').classList.add('d-none');
        document.getElementById('skill-list').classList.remove('d-none');
    });
</script>
@endpush
