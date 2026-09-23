{{--
    Floating help on the public job pages: a small button that opens a panel
    with a short FAQ and how to reach HR (config/help.php). It can be dismissed
    for the rest of the visit.

    Each part of the panel is a <section data-help-view>, so a direct HR
    contact channel can be added later as one more section and one more entry
    in the menu, without changing how the bubble works.
--}}
@php
    $hr = array_filter(config('help.hr', []));
    $faq = config('help.faq', []);
@endphp
<div class="zn-help-bubble {{ ($raised ?? false) ? 'is-raised' : '' }}" id="zn-help" data-open="false">
    <div class="zn-help-panel" id="zn-help-panel" role="dialog" aria-modal="false" aria-labelledby="zn-help-title" hidden>
        <div class="zn-help-panel-head">
            <b id="zn-help-title">Need help?</b>
            <button type="button" class="zn-help-x" data-help-close aria-label="Close help"><i class="bi bi-x-lg"></i></button>
        </div>

        <section data-help-view="menu">
            <button type="button" class="zn-help-item" data-help-go="faq">
                <i class="bi bi-question-circle"></i>
                <span><b>Frequently asked questions</b><small>Applying, documents, your application</small></span>
            </button>
            <button type="button" class="zn-help-item" data-help-go="hr">
                <i class="bi bi-person-lines-fill"></i>
                <span><b>HR assistance</b><small>How to reach our HR team</small></span>
            </button>
        </section>

        <section data-help-view="faq" hidden>
            <button type="button" class="zn-link zn-help-back" data-help-go="menu">&larr; Back</button>
            @foreach ($faq as $question => $answer)
                <details class="zn-help-faq">
                    <summary>{{ $question }}</summary>
                    <p>{{ $answer }}</p>
                </details>
            @endforeach
        </section>

        <section data-help-view="hr" hidden>
            <button type="button" class="zn-link zn-help-back" data-help-go="menu">&larr; Back</button>
            <p class="zn-help-text">Questions about a position or your application? Our HR team can help.</p>
            @if (!empty($hr['email']))
                <a class="zn-help-contact" href="mailto:{{ $hr['email'] }}"><i class="bi bi-envelope"></i> {{ $hr['email'] }}</a>
            @endif
            @if (!empty($hr['phone']))
                <a class="zn-help-contact" href="tel:{{ preg_replace('/[^0-9+]/', '', $hr['phone']) }}"><i class="bi bi-telephone"></i> {{ $hr['phone'] }}</a>
            @endif
            @if (!empty($hr['hours']))
                <p class="zn-help-text"><i class="bi bi-clock"></i> {{ $hr['hours'] }}</p>
            @endif
            @auth
                <a class="zn-help-contact" href="{{ route('applications.index') }}"><i class="bi bi-briefcase"></i> See my applications</a>
            @endauth
        </section>
    </div>

    <div class="zn-help-launch">
        <button type="button" class="zn-help-dismiss" data-help-dismiss aria-label="Hide help for this visit" title="Hide">
            <i class="bi bi-x"></i>
        </button>
        <button type="button" class="zn-help-btn" id="zn-help-btn" aria-expanded="false" aria-controls="zn-help-panel">
            <i class="bi bi-chat-dots-fill" aria-hidden="true"></i><span>Help</span>
        </button>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const root = document.getElementById('zn-help');
        if (!root) return;

        const KEY = 'zn-help-dismissed';
        try { if (sessionStorage.getItem(KEY) === '1') { root.hidden = true; return; } } catch (e) {}

        const panel = document.getElementById('zn-help-panel');
        const btn = document.getElementById('zn-help-btn');

        function show(view) {
            panel.querySelectorAll('[data-help-view]').forEach(s => { s.hidden = s.dataset.helpView !== view; });
        }
        function setOpen(open) {
            panel.hidden = !open;
            root.dataset.open = open ? 'true' : 'false';
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) show('menu');
        }

        btn.addEventListener('click', () => setOpen(panel.hidden));
        root.querySelector('[data-help-close]').addEventListener('click', () => { setOpen(false); btn.focus(); });
        root.querySelectorAll('[data-help-go]').forEach(b => b.addEventListener('click', () => show(b.dataset.helpGo)));
        root.querySelector('[data-help-dismiss]').addEventListener('click', function () {
            root.hidden = true;
            try { sessionStorage.setItem(KEY, '1'); } catch (e) {}
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !panel.hidden) { setOpen(false); btn.focus(); }
        });
    })();
</script>
@endpush
