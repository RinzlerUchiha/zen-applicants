{{--
    Unsaved-changes guard for the Application Form sections.

    Watches every form marked data-unsaved-guard (Personal details, and the
    add / edit entry form in each list section). A form is only watched while it
    is actually open for editing — Personal details after "Edit", an entry form
    while it is showing — and its values are remembered at that moment, so
    "changed" means changed by the applicant, not by the page filling it in.

    Leaving through a link while something has changed opens this dialog, lists
    the changed fields and highlights them on the page:
      Save & continue  → the form's own submit, which then goes where they were
                         heading (ContinueAfterSave); a failed save stays here
      Discard changes  → leave without saving
      Cancel           → stay, fields still highlighted
    Closing or reloading the tab gets the browser's own warning instead.
    Nothing changes for an applicant who has not edited anything.
--}}
<div class="modal fade" id="unsavedModal" data-bs-backdrop="static" tabindex="-1"
     aria-labelledby="unsavedModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content zn-modal">
            <div class="modal-header">
                <h1 class="modal-title" id="unsavedModalTitle">You have unsaved changes</h1>
            </div>
            <div class="modal-body">
                <p class="zn-modal-lede" style="margin-bottom:10px">
                    If you leave now, your changes to these fields will be lost:
                </p>
                <ul class="zn-unsaved-list" id="unsavedModalList"></ul>
                <p class="zn-help" style="margin:10px 0 0">They are highlighted on the page.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm" data-unsaved="cancel">Cancel</button>
                <button type="button" class="zn-btn zn-btn-out zn-btn-sm zn-btn-warn" data-unsaved="discard">Discard changes</button>
                <button type="button" class="zn-btn zn-btn-sm" data-unsaved="save">Save &amp; continue</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const forms = Array.from(document.querySelectorAll('form[data-unsaved-guard]'));
    const modalEl = document.getElementById('unsavedModal');
    if (!forms.length || !modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const listEl = document.getElementById('unsavedModalList');
    const snapshots = new Map();   // form -> Map(field -> value when editing began)
    let leaving = false;           // a save or a confirmed discard is under way
    let pending = null;            // { href } or { action } to run after the dialog
    let highlighting = false;

    function fieldsOf(form) {
        return Array.from(form.elements).filter(function (el) {
            return el.name && el.name !== '_token' && el.name !== '_after_save'
                && !['hidden', 'submit', 'button', 'reset'].includes(el.type);
        });
    }

    // Masked government IDs swap between dots and the real number on focus;
    // that is display, not an edit, so both forms count as the same value.
    function valueOf(el) {
        if (el.type === 'checkbox' || el.type === 'radio') return el.checked ? '1' : '';
        if (el.type === 'file') return Array.from(el.files || []).map(f => f.name).join('|');
        if (el.dataset.real && el.value === el.dataset.masked) return el.dataset.real;
        return el.value;
    }

    function isOpen(form) {
        return !form.closest('.d-none') && fieldsOf(form).some(el => !el.matches(':disabled'));
    }

    function sync() {
        forms.forEach(function (form) {
            const open = isOpen(form);
            if (open && !snapshots.has(form)) {
                snapshots.set(form, new Map(fieldsOf(form).map(el => [el, valueOf(el)])));
            } else if (!open && snapshots.has(form)) {
                snapshots.delete(form);
                clearHighlights(form);
            }
        });
    }

    function changedFields(form) {
        const snap = snapshots.get(form);
        if (!snap || !isOpen(form)) return [];
        const seen = new Set();
        return fieldsOf(form).filter(function (el) {
            if (!snap.has(el) || valueOf(el) === snap.get(el)) return false;
            const key = el.type === 'radio' ? 'radio:' + el.name : el;
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    }

    function dirtyForms() {
        sync();
        return forms.filter(form => changedFields(form).length);
    }

    function labelFor(el) {
        const label = (el.id && document.querySelector('label[for="' + CSS.escape(el.id) + '"]'))
            || el.closest('.zn-fld')?.querySelector('label');
        if (!label) return el.name;
        const copy = label.cloneNode(true);
        copy.querySelectorAll('.zn-req, .zn-opt').forEach(n => n.remove());
        return copy.textContent.replace(/\s+/g, ' ').trim() || el.name;
    }

    function clearHighlights(form) {
        form.querySelectorAll('.zn-unsaved').forEach(n => n.classList.remove('zn-unsaved'));
    }

    function highlight() {
        forms.forEach(function (form) {
            clearHighlights(form);
            changedFields(form).forEach(function (el) {
                (el.closest('.zn-fld') || el).classList.add('zn-unsaved');
            });
        });
    }

    function ask(next) {
        const dirty = dirtyForms();
        if (!dirty.length) return false;

        pending = next;
        highlighting = true;
        highlight();

        listEl.replaceChildren(...dirty.flatMap(changedFields).map(function (el) {
            const li = document.createElement('li');
            li.textContent = labelFor(el);
            return li;
        }));

        modal.show();
        return true;
    }

    function proceed() {
        leaving = true;
        if (pending.href) window.location.href = pending.href;
        else if (pending.action) pending.action();
    }

    // Links that leave the section: sidebar, Back / Next, header, anything else.
    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || leaving) return;
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.target && link.target !== '_self') return;
        if (link.hasAttribute('download')) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

        const url = new URL(link.href, window.location.href);
        if (url.origin === location.origin && url.pathname === location.pathname && url.search === location.search && url.hash) return;

        if (ask({ href: link.href })) event.preventDefault();
    }, true);

    // Other forms that navigate away (Sign out). They cannot carry the
    // section's data, so Save & continue saves and stays; Discard proceeds.
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (leaving) return;
        if (forms.includes(form)) { leaving = true; return; }   // this section's own Save
        if (ask({ action: () => HTMLFormElement.prototype.submit.call(form), stays: true })) event.preventDefault();
    }, true);

    modalEl.addEventListener('click', function (event) {
        const choice = event.target.closest('[data-unsaved]')?.dataset.unsaved;
        if (!choice || !pending) return;

        if (choice === 'cancel') {
            pending = null;
            modal.hide();
            const first = document.querySelector('.zn-unsaved');
            first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        if (choice === 'discard') {
            modal.hide();
            proceed();
            return;
        }

        // Save & continue: submit the changed form through its normal save.
        const form = dirtyForms()[0];
        if (!form) { proceed(); return; }

        // An incomplete record cannot be saved, so there is nothing to continue
        // to: close the dialog, then point at what is missing and stay here.
        // The message waits for the dialog to be gone — asking for it mid-close
        // leaves the dialog open on top of the field it is pointing at.
        if (!form.checkValidity()) {
            pending = null;
            modalEl.addEventListener('hidden.bs.modal', () => form.reportValidity(), { once: true });
            modal.hide();
            return;
        }

        if (pending.href) {
            let input = form.querySelector('input[name="_after_save"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_after_save';
                form.appendChild(input);
            }
            input.value = pending.href;
        }

        leaving = true;
        modal.hide();
        form.requestSubmit();
    });

    // Buttons that deliberately throw edits away (Personal details' Cancel
    // reloads the page) are not a loss to warn about.
    document.querySelectorAll('[data-unsaved-discard]').forEach(function (button) {
        button.addEventListener('click', () => { leaving = true; }, true);
    });

    ['input', 'change'].forEach(function (type) {
        document.addEventListener(type, function (event) {
            if (highlighting && forms.some(form => form.contains(event.target))) highlight();
        });
    });

    window.addEventListener('beforeunload', function (event) {
        if (leaving || !dirtyForms().length) return;
        event.preventDefault();
        event.returnValue = '';
    });

    // A form opens for editing when its wrapper is shown or its fieldset is
    // enabled; remember its values at that moment.
    new MutationObserver(() => setTimeout(sync, 0))
        .observe(document.body, { subtree: true, attributes: true, attributeFilter: ['class', 'disabled'] });
    sync();

    // For tests and debugging only.
    window.__znUnsaved = { dirtyForms, changedFields, sync };
})();
</script>
@endpush
