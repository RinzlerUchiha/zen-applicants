/*
 * Shared form helpers for the Applicants portal.
 *
 *  1. "Same as permanent address" — any checkbox with data-same-as-to="<block>"
 *     copies the permanent address (personal-padd-*) into that block
 *     (personal-cadd-*, personal-badd-*) and keeps it in step while ticked.
 *     Copied fields are locked while ticked and released before the form is
 *     sent, so the values are submitted exactly as if typed.
 *
 *  2. Barangay fallback — the reference list does not have barangays for
 *     every city. When the chosen city has none listed, the Barangay
 *     dropdown is replaced by a text box under the same field name, so the
 *     address can still be completed. Nothing about storage changes.
 *
 *  3. Calendar — every <input type="date"> gets a themed Flatpickr calendar.
 *     The original input is kept (hidden) and still holds the value in
 *     YYYY-MM-DD, so what is submitted, the server's validation and the
 *     page's own scripts are unchanged: setting input.value, toggling
 *     disabled/required, and listening for "change" all still work on it.
 *     Date fields a page adds later (e.g. a new row) get it too.
 *
 * Shared: ZenHub's HireFlow module loads a copy of this file
 * (zen/manpower/assets/zn-forms.js, written by the theme build — edit this one).
 */
(function () {
    'use strict';

    /* ------------------------------------------------ same as permanent -- */

    const PARTS = ['province', 'city', 'barangay', 'specific'];
    const field = (block, part) => document.getElementById('personal-' + block + '-' + part);

    function initSameAs(box) {
        const to = box.dataset.sameAsTo;
        const from = box.dataset.sameAsFrom || 'padd';
        const pairs = PARTS.map(p => [field(from, p), field(to, p)]).filter(([a, b]) => a && b);
        if (!pairs.length) return;

        function mirror() {
            pairs.forEach(function ([source, target]) {
                if (target.tagName === 'SELECT') {
                    // City and barangay lists only show the options for the
                    // chosen province / city — reveal the one being copied.
                    target.querySelectorAll('option').forEach(function (option) {
                        if (option.value === source.value) option.style.display = '';
                    });
                }
                target.value = source.value;
                target.disabled = box.checked;
            });
        }

        box.addEventListener('change', function () {
            if (box.checked) {
                mirror();
            } else {
                pairs.forEach(([, target]) => { target.disabled = false; });
            }
        });

        // Keep the copy live while the box stays ticked.
        pairs.forEach(function ([source]) {
            source.addEventListener('change', () => { if (box.checked) mirror(); });
            source.addEventListener('input', () => { if (box.checked) mirror(); });
        });

        // Disabled fields are not submitted, so release them just before send.
        const form = box.form || box.closest('form') || pairs[0][1].form;
        if (form) {
            form.addEventListener('submit', function () {
                pairs.forEach(([, target]) => { target.disabled = false; });
            });
        }

        // A saved address that already matches shows as ticked.
        const same = pairs.every(([a, b]) => a.value === b.value) && pairs[0][0].value !== '';
        if (same && !box.checked) {
            box.checked = true;
        }
        if (box.checked) {
            mirror();
        }
    }

    /* ------------------------------------------------- barangay fallback -- */

    function initFallback(select) {
        const typed = document.querySelector('.zn-typed-fallback[data-for="' + CSS.escape(select.id) + '"]');
        if (!typed) return;

        const name = select.name;

        function apply() {
            // The lists are filtered by the parent choice: an option is
            // "available" when it is not hidden.
            const options = [...select.options].filter(o => o.value !== '' && o.style.display !== 'none');
            const listed = options.length > 0;

            select.hidden = !listed;
            typed.hidden = listed;
            select.name = listed ? name : '';
            typed.name = listed ? '' : name;
            if (listed && typed.value && !select.value) {
                // A typed barangay that now exists in the list: match it.
                const match = options.find(o => o.value.toLowerCase() === typed.value.trim().toLowerCase());
                if (match) select.value = match.value;
            }
        }

        const parent = select.closest('.zn-grid')?.querySelector('.select-city');
        parent?.addEventListener('change', () => setTimeout(apply, 0));
        apply();
    }

    /* --------------------------------------------------------- calendar -- */

    function initDate(input) {
        if (input.dataset.znDate || typeof window.flatpickr !== 'function') return;
        input.dataset.znDate = '1';

        const fp = window.flatpickr(input, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'M j, Y',
            // The visible field keeps the original's own classes, so the
            // form's input styling applies unchanged (not Bootstrap's).
            altInputClass: (input.className + ' zn-date').trim(),
            allowInput: true,
            minDate: input.min || null,
            maxDate: input.max || null,
            // Phones keep their own date wheel, which is the better picker there.
            disableMobile: false,
        });

        const display = fp.altInput;
        if (!display) return; // native mobile input in use

        // The page's own scripts still write input.value — show what they write.
        const proto = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
        let syncing = false;
        Object.defineProperty(input, 'value', {
            configurable: true,
            get() { return proto.get.call(this); },
            set(v) {
                proto.set.call(this, v);
                if (syncing) return;
                syncing = true;
                try { fp.setDate(v || null, false, 'Y-m-d'); } finally { syncing = false; }
            },
        });

        // ...and the states they set (disabled, required, error styling).
        function copyState() {
            display.disabled = input.disabled;
            display.required = input.required;
            display.classList.toggle('is-invalid', input.classList.contains('is-invalid'));
        }
        copyState();
        new MutationObserver(copyState).observe(input, { attributes: true, attributeFilter: ['disabled', 'required', 'class'] });

        // The label points at the original input; send its click to the calendar.
        if (input.id) {
            document.querySelectorAll('label[for="' + CSS.escape(input.id) + '"]').forEach(function (label) {
                display.setAttribute('aria-label', label.textContent.trim().replace(/\s*\*$/, ''));
                label.addEventListener('click', function (e) { e.preventDefault(); display.focus(); });
            });
        }
        display.setAttribute('placeholder', input.getAttribute('placeholder') || 'Select a date');
    }

    function initAll(root) {
        root.querySelectorAll('input[type="date"]').forEach(initDate);
        root.querySelectorAll('input[type="checkbox"][data-same-as-to]').forEach(initSameAs);
        root.querySelectorAll('select.select-barangay').forEach(initFallback);
    }

    function start() {
        initAll(document);
        new MutationObserver(function (changes) {
            changes.forEach(function (change) {
                change.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) return;
                    if (node.matches('input[type="date"]')) initDate(node);
                    else if (node.querySelector('input[type="date"]')) node.querySelectorAll('input[type="date"]').forEach(initDate);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
