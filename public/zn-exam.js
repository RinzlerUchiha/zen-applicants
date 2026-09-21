/*
 * The exam frame shared by all eleven assessments (HireFlow 2.5).
 *
 * The server keeps the clock and decides everything; this script keeps the page
 * in step with it and makes the rules visible:
 *   - the time left, counted against the wall clock (right after sleep or an
 *     offline spell) and re-synced on every check-in
 *   - check-ins every few seconds carrying the current answers (autosave);
 *     offline, the answers stay on the page and are saved on reconnect
 *   - one window at a time: another window taking over locks this one
 *   - fullscreen as a deterrent, where the browser supports it: leaving it
 *     covers the questions until the applicant returns. The clock keeps
 *     running, nothing is recorded, nothing is failed.
 *   - a question map (answered / not yet), and a confirmation before submit
 *   - at time-up the answers freeze and are submitted, retrying until the
 *     server is reached
 *
 * Each assessment page calls ZnExam.init({ ... }) with:
 *   payload()    exactly what its Submit sends ({ set: ... })       required
 *   items()      [{ el, answered }] one per question, for the map    optional
 *   progress()   a short text, where "answered" does not apply       optional
 *   go(i)        show item i (pages that show one item at a time)   optional
 *   requireAll   every item must be answered before submitting      optional
 *   check()      extra rule before submitting: a message, or null   optional
 */
(function () {
    'use strict';

    const configEl = document.getElementById('zn-exam-config');
    if (!configEl) return;

    const config = JSON.parse(configEl.textContent);
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const $ = (id) => document.getElementById(id);

    let options = null;
    let deadline = Date.now() + config.remaining * 1000; // wall clock
    let dirty = false;
    let saving = false;
    let locked = false;      // another window / finished: nothing more here
    let submitting = false;
    let timeUp = false;
    let online = navigator.onLine !== false;
    let tick = null;
    let beat = null;
    let debounce = null;

    const remaining = () => Math.max(0, Math.round((deadline - Date.now()) / 1000));

    /* ---------------------------------------------------------------- clock */

    function fmt(seconds) {
        const m = Math.floor(seconds / 60);
        return m + ':' + String(seconds % 60).padStart(2, '0');
    }

    function renderClock() {
        const el = $('zn-exam-timer');
        if (!el) return;
        const left = remaining();
        el.textContent = fmt(left);
        const bar = el.closest('.zn-exam-clock');
        if (bar) {
            bar.classList.toggle('is-low', left <= 300 && left > 60);
            bar.classList.toggle('is-critical', left <= 60);
        }
    }

    /* ---------------------------------------------------- progress and map */

    function items() {
        return options && options.items ? options.items() : null;
    }

    function unanswered() {
        const list = items();
        return list ? list.map((it, i) => ({ ...it, i })).filter((it) => !it.answered) : [];
    }

    function renderProgress() {
        const text = $('zn-exam-progress');
        const bar = $('zn-exam-progress-bar');
        const list = items();

        if (list) {
            const done = list.filter((it) => it.answered).length;
            if (text) text.textContent = done + ' of ' + list.length + ' answered';
            if (bar) bar.style.width = (list.length ? Math.round(done / list.length * 100) : 0) + '%';
            renderMap(list);
            return;
        }
        const note = options && options.progress ? options.progress() : '';
        if (text) text.textContent = note;
        if (bar) bar.parentElement.hidden = true;
        const toggle = $('zn-exam-map-toggle');
        if (toggle) toggle.hidden = true;
    }

    function renderMap(list) {
        const map = $('zn-exam-map-list');
        if (!map) return;
        if (map.childElementCount !== list.length) {
            map.innerHTML = '';
            list.forEach((it, i) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = String(i + 1);
                b.addEventListener('click', () => goTo(i));
                map.appendChild(b);
            });
        }
        list.forEach((it, i) => {
            const b = map.children[i];
            b.classList.toggle('is-answered', !!it.answered);
            b.setAttribute('aria-label', 'Question ' + (i + 1) + (it.answered ? ', answered' : ', not answered'));
        });
        const next = $('zn-exam-next-unanswered');
        if (next) next.hidden = unanswered().length === 0;
    }

    function goTo(i) {
        const list = items();
        if (!list || !list[i]) return;
        if (options.go) options.go(i);
        const el = list[i].el;
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.add('zn-exam-flash');
        setTimeout(() => el.classList.remove('zn-exam-flash'), 1200);
        const map = $('zn-exam-map');
        if (map && window.matchMedia('(max-width: 720px)').matches) map.classList.remove('is-open');
    }

    function goToFirstUnanswered() {
        const first = unanswered()[0];
        if (first) goTo(first.i);
    }

    /* -------------------------------------------------------------- saving */

    function renderSaved(text, state) {
        const el = $('zn-exam-saved');
        if (!el) return;
        el.textContent = text;
        el.dataset.state = state || '';
    }

    async function post(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body),
        });
        let data = {};
        try { data = await response.json(); } catch (e) { /* non-JSON error */ }
        return { status: response.status, data };
    }

    /* Check in with the server: count time, autosave, learn the state. */
    async function ping() {
        if (locked || saving || submitting || timeUp) return;
        saving = true;
        const wasDirty = dirty;
        dirty = false;
        try {
            const { status, data } = await post(config.pingUrl, {
                attempt_token: config.token,
                payload: options ? options.payload() : null,
            });
            if (status !== 200) throw new Error('HTTP ' + status);

            if (data.state === 'active') {
                deadline = Date.now() + data.remaining * 1000;
                renderClock();
                renderSaved(data.saved_at ? 'All answers saved · ' + data.saved_at : 'All answers saved', 'ok');
            } else if (data.state === 'other_tab') {
                otherWindow();
            } else {
                // interrupted, timed out, submitted — the page explains which.
                locked = true;
                window.location.reload();
            }
        } catch (e) {
            dirty = dirty || wasDirty;
            renderSaved(online
                ? 'Not saved yet — retrying…'
                : 'Offline — your answers stay on this page and save when you reconnect. The timer keeps running.', 'error');
        } finally {
            saving = false;
        }
    }

    function changed() {
        if (locked || timeUp) return;
        dirty = true;
        renderSaved(online ? 'Saving…' : 'Offline — will save when you reconnect', online ? 'pending' : 'error');
        renderProgress();
        clearTimeout(debounce);
        debounce = setTimeout(ping, 800);
    }

    /* ------------------------------------------------------------ overlays */

    function overlay(id, show) {
        const el = $(id);
        if (el) el.hidden = !show;
        document.body.classList.toggle('zn-exam-covered',
            ['zn-exam-lock', 'zn-exam-fullscreen', 'zn-exam-submitting'].some((o) => $(o) && !$(o).hidden));
    }

    function otherWindow() {
        if (locked) return;
        locked = true;
        clearInterval(beat);
        overlay('zn-exam-fullscreen', false);
        overlay('zn-exam-lock', true);
        const btn = $('zn-exam-lock').querySelector('[data-lock-action]');
        btn.onclick = () => window.location.reload();
    }

    /* ---------------------------------------------------------- fullscreen */

    const fs = {
        supported: !!(document.documentElement.requestFullscreen && document.fullscreenEnabled !== false),
        declined: false,      // the browser refused: carry on without it
        entered: false,
        active: () => !!document.fullscreenElement,
    };

    function fullscreenGate() {
        if (!fs.supported || fs.declined || locked || submitting) return;
        if (fs.active()) {
            overlay('zn-exam-fullscreen', false);
            return;
        }
        const box = $('zn-exam-fullscreen');
        if (!box) return;
        box.querySelector('[data-fs-title]').textContent = fs.entered
            ? 'You left fullscreen'
            : 'This assessment runs in fullscreen';
        box.querySelector('[data-fs-body]').textContent = fs.entered
            ? 'The questions are hidden until you return. The timer is still running and your answers are saved.'
            : 'Fullscreen keeps other windows out of the way while you work. Press Esc at any time to leave it — you can come straight back.';
        box.querySelector('[data-fs-action]').textContent = fs.entered ? 'Return to fullscreen' : 'Enter fullscreen';
        box.querySelector('[data-fs-skip]').hidden = true;
        overlay('zn-exam-fullscreen', true);
    }

    async function enterFullscreen() {
        try {
            await document.documentElement.requestFullscreen({ navigationUI: 'hide' });
            fs.entered = true;
            overlay('zn-exam-fullscreen', false);
        } catch (e) {
            // Blocked by the browser or device: not the applicant's doing.
            const skip = $('zn-exam-fullscreen').querySelector('[data-fs-skip]');
            skip.hidden = false;
            $('zn-exam-fullscreen').querySelector('[data-fs-body]').textContent =
                'Your browser did not allow fullscreen. You can continue without it.';
        }
    }

    /* -------------------------------------------------------------- submit */

    async function confirmSubmit() {
        const list = items();
        const missing = unanswered();
        const extra = options.check ? options.check() : null;
        const blocked = (options.requireAll && missing.length) || extra;

        const note = $('zn-exam-confirm-note');
        const yes = $('zn-exam-confirm').querySelector('[data-confirm]');
        if (blocked) {
            note.textContent = extra || ('Answer every item before submitting — ' + missing.length + ' still '
                + (missing.length === 1 ? 'needs' : 'need') + ' an answer.');
            yes.textContent = missing.length ? 'Go to the first one' : 'OK';
        } else if (list) {
            note.textContent = missing.length
                ? 'You have answered ' + (list.length - missing.length) + ' of ' + list.length + '. Unanswered items score nothing.'
                : 'You have answered all ' + list.length + '.';
            yes.textContent = 'Submit answers';
        } else {
            note.textContent = (options.progress ? options.progress() + '. ' : '') + 'Check your answers before you submit.';
            yes.textContent = 'Submit answers';
        }

        const modalEl = $('zn-exam-confirm');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const chosen = await new Promise((resolve) => {
            const onYes = () => { resolve(true); modal.hide(); };
            yes.addEventListener('click', onYes, { once: true });
            modalEl.addEventListener('hidden.bs.modal', () => {
                yes.removeEventListener('click', onYes);
                resolve(false);
            }, { once: true });
            modal.show();
        });

        if (chosen && blocked) {
            if (missing.length) goToFirstUnanswered();
            return false;
        }
        return chosen;
    }

    async function submit(auto) {
        if (locked || submitting || !options) return;
        if (!auto && !(await confirmSubmit())) return;

        submitting = true;
        clearTimeout(debounce);
        const body = Object.assign({}, options.payload(), { attempt_token: config.token });
        const text = $('zn-exam-submitting').querySelector('[data-submitting-text]');
        text.textContent = auto ? "Time's up — submitting your answers…" : 'Submitting your answers…';
        overlay('zn-exam-fullscreen', false);
        overlay('zn-exam-submitting', true);

        for (let wait = 2000; ; wait = Math.min(wait * 2, 30000)) {
            try {
                const { status, data } = await post(config.submitUrl, body);
                if (data.success || status === 409 || status === 410) {
                    // Done, or already settled (finished, interrupted,
                    // another window): the page explains which.
                    dirty = false;
                    locked = true;
                    if (document.fullscreenElement) document.exitFullscreen().catch(() => {});
                    window.location.reload();
                    return;
                }
                if (!auto) {
                    // Something to correct (422): say what, stay.
                    overlay('zn-exam-submitting', false);
                    submitting = false;
                    alert(Array.isArray(data.error) ? data.error.join('\n') : (data.message || 'Please check your answers and try again.'));
                    fullscreenGate();
                    return;
                }
                throw new Error('HTTP ' + status);
            } catch (e) {
                if (!auto) {
                    overlay('zn-exam-submitting', false);
                    submitting = false;
                    alert('Could not reach the server. Your answers are kept on this page — check your connection and press Submit again.');
                    fullscreenGate();
                    return;
                }
                // Time is up: the answers are frozen; keep trying until the
                // server is reached. They count when they arrive.
                text.textContent = "Time's up — your answers are ready and will be submitted as soon as you're back online.";
                await new Promise((r) => setTimeout(r, wait));
            }
        }
    }

    function timeIsUp() {
        if (timeUp || locked) return;
        timeUp = true;
        clearInterval(tick);
        clearInterval(beat);
        // Freeze the answers where they are.
        document.querySelectorAll('.zn-assess-body input, .zn-assess-body select, .zn-assess-body textarea, .zn-assess-body button')
            .forEach((el) => { el.disabled = true; });
        submitting = false;
        submit(true);
    }

    /* ---------------------------------------------------------------- init */

    window.ZnExam = {
        init(opts) {
            options = opts;
            const root = document.querySelector('.zn-assess-body');
            if (root) {
                ['change', 'input'].forEach((type) => root.addEventListener(type, changed));
                root.querySelectorAll('form').forEach((f) => f.setAttribute('novalidate', ''));
            }

            const mapToggle = $('zn-exam-map-toggle');
            if (mapToggle) mapToggle.addEventListener('click', () => {
                const open = $('zn-exam-map').classList.toggle('is-open');
                mapToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            const next = $('zn-exam-next-unanswered');
            if (next) next.addEventListener('click', goToFirstUnanswered);
            const barSubmit = $('zn-exam-submit');
            if (barSubmit) barSubmit.addEventListener('click', () => submit(false));

            renderClock();
            renderProgress();
            tick = setInterval(() => {
                if (locked || submitting) return;
                renderClock();
                if (remaining() <= 0) timeIsUp();
            }, 250);
            beat = setInterval(ping, config.heartbeat * 1000);
            ping();

            window.addEventListener('online', () => { online = true; ping(); });
            window.addEventListener('offline', () => {
                online = false;
                renderSaved('Offline — your answers stay on this page and save when you reconnect. The timer keeps running.', 'error');
            });

            // Fullscreen: offered at the start, asked for again if left.
            if (fs.supported) {
                const box = $('zn-exam-fullscreen');
                box.querySelector('[data-fs-action]').addEventListener('click', enterFullscreen);
                box.querySelector('[data-fs-skip]').addEventListener('click', () => {
                    fs.declined = true;
                    overlay('zn-exam-fullscreen', false);
                });
                document.addEventListener('fullscreenchange', fullscreenGate);
                fullscreenGate();
            }

            // Another window of the same assessment in this browser: the newest
            // one takes over at once (the server enforces it regardless).
            if ('BroadcastChannel' in window) {
                const channel = new BroadcastChannel('zn-exam-' + config.key);
                channel.postMessage({ token: config.token });
                channel.onmessage = (e) => {
                    if (e.data && e.data.token && e.data.token !== config.token) otherWindow();
                };
            }

            window.addEventListener('beforeunload', (e) => {
                if ((dirty || timeUp) && !locked) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },
        /* For widgets that change answers without a change event (drag-and-drop, dialogs). */
        changed,
        submit: () => submit(false),
        /* Helpers for items(): one entry per element, answered when test(el) is true. */
        each(elements, test) {
            return Array.from(elements).map((el) => ({ el, answered: !!test(el) }));
        },
        hasChecked(el) {
            return !!el.querySelector('input:checked');
        },
    };
})();
