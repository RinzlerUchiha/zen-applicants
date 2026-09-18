{{--
    The applicant's sidebar, shared by the Application Form, Documents and
    Assessments.

    Two groups, and the difference between them matters:

      Application Form   the eight sections of ONE form. Numbered, ticked as
                         they are completed, and the only thing the percentage
                         counts.
      Screening          Documents and Assessments. Separate areas of the
                         hiring process, not sections 9 and 10: no numbers, and
                         they never affect the Application Form percentage.

    The data comes from the view composer in AppServiceProvider, which is bound
    to this partial — so it is available wherever the partial is included.

    On a phone the whole list collapses behind the summary button, so the page
    itself is the first thing on screen.
--}}
@php
    $railSections = collect($formSections ?? []);
    $railKeys = $railSections->keys()->values();
    $railCurrent = $railSections->search(fn ($section) => request()->routeIs($section['route'])) ?: null;
    $railIndex = $railKeys->search($railCurrent);

    $screening = [
        [
            'label' => 'Documents',
            'route' => 'documents.index',
            'icon' => 'bi-folder2-open',
            'active' => request()->routeIs('documents.*'),
        ],
        [
            'label' => 'Assessments',
            'route' => 'assessments.index',
            'icon' => 'bi-ui-checks',
            'active' => request()->routeIs('assessments.*', 'enneagram.*', 'tapt.*', 'disc.*', 'miq.*',
                'color.*', 'vak.*', 'why_i_work.*', 'career_anchors.*', 'abstract_reasoning.*', 'basic_math.*', 'maya.*'),
        ],
    ];
@endphp

<aside class="zn-form-rail" aria-label="Applicant sections">
    <button type="button" class="zn-form-rail-toggle" aria-expanded="false" aria-controls="zn-form-rail-body">
        <span>
            <b>Application Form &mdash; {{ $formPercent ?? 0 }}% complete</b>
            <span>
                @if ($railIndex !== false)
                    Section {{ $railIndex + 1 }} of {{ $railKeys->count() }}
                @else
                    {{ collect($screening)->firstWhere('active', true)['label'] ?? 'Menu' }}
                @endif
            </span>
        </span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>

    <div class="zn-form-rail-body" id="zn-form-rail-body">
        <div class="zn-form-rail-progress">
            <div class="t"><span>Application Form</span><b>{{ $formPercent ?? 0 }}%</b></div>
            <div class="zn-bar"><i style="width: {{ $formPercent ?? 0 }}%"></i></div>
            <span>{{ $formCounts['done'] ?? 0 }} of {{ $formCounts['total'] ?? 3 }} required sections complete</span>
        </div>

        <ol class="zn-form-rail-list">
            @foreach ($railSections as $key => $section)
                @php
                    // An optional section with nothing in it counts as "complete"
                    // for the application, but it has not been done — so it is
                    // only ticked once it has at least one entry.
                    $done = $section['complete'] && ($section['blocking'] || ($section['rows'] ?? 0) > 0);
                    $state = $done ? 'done' : ($section['started'] ? 'part' : 'todo');
                    $stateText = ['done' => 'Complete', 'part' => 'In progress', 'todo' => 'Not started'][$state];
                @endphp
                <li>
                    <a class="zn-form-rail-item {{ $state }} {{ $key === $railCurrent ? 'active' : '' }}"
                       href="{{ Route::has($section['route']) ? route($section['route']) : '#' }}"
                       @if ($key === $railCurrent) aria-current="step" @endif>
                        <span class="zn-form-rail-mark" aria-hidden="true">
                            @if ($done)
                                <i class="bi bi-check-lg"></i>
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </span>
                        <span class="zn-form-rail-label">
                            {{ $section['label'] }}
                            @unless ($section['blocking'])
                                <em>Optional</em>
                            @endunless
                        </span>
                        <span class="visually-hidden">— {{ $stateText }}</span>
                        @if (!$section['complete'] && $section['blocking'] && count($section['missing']))
                            <span class="zn-form-rail-count" title="{{ count($section['missing']) }} still needed">{{ count($section['missing']) }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ol>

        {{-- Separate areas, deliberately outside the numbered list and outside
             the progress above. --}}
        <p class="zn-form-rail-group" id="zn-rail-screening">Screening</p>
        <ul class="zn-form-rail-list zn-form-rail-screening" aria-labelledby="zn-rail-screening">
            @foreach ($screening as $item)
                <li>
                    <a class="zn-form-rail-item {{ $item['active'] ? 'active' : '' }}"
                       href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       @if ($item['active']) aria-current="page" @endif>
                        <span class="zn-form-rail-mark" aria-hidden="true"><i class="bi {{ $item['icon'] }}"></i></span>
                        <span class="zn-form-rail-label">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</aside>

@once
    @push('scripts')
    <script>
        (function () {
            const toggle = document.querySelector('.zn-form-rail-toggle');
            const rail = document.querySelector('.zn-form-rail');
            if (!toggle) return;
            toggle.addEventListener('click', function () {
                const open = rail.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        })();
    </script>
    @endpush
@endonce
