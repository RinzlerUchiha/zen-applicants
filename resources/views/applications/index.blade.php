@extends('layouts.app')

@section('title', 'My Applications')

@section('body')
<main class="zn-canvas">
    <div class="zn-narrow">

        @if (session('success'))
            <div class="zn-toast"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="zn-toast error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="zn-toast error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="mb-3">
            <p class="zn-page-title" style="margin-bottom:2px">My applications</p>
            <p class="zn-page-sub" style="margin-bottom:0">Everything you've applied to, and where each one stands.</p>
        </div>

        @php
            $openApplications = $applications->reject(fn ($a) => $a->is_closed);
        @endphp

        @forelse ($applications as $application)
            @php
                // Whether it is closed, and what that closure means, come from the
                // one status table (config/applications.php) — nothing here keeps
                // its own list of closed statuses.
                $isClosed = $application->is_closed;
                $closedOn = $application->closed_at?->format('F j, Y');
                $otherOpen = $openApplications->where('id', '!=', $application->id);
            @endphp

            <div class="zn-card mb-2 zn-application {{ $isClosed ? 'is-closed' : '' }}" data-application="{{ $application->id }}">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div>
                        <h4 style="font-size:15.5px;font-weight:700;margin:0;letter-spacing:-.2px">{{ $application->job_title }}</h4>
                        <p class="zn-count" style="font-weight:400;margin:3px 0 0">
                            {{ $application->mr_no }} · Applied {{ $application->applied_at?->format('F j, Y') }}
                        </p>
                    </div>
                    <span class="zn-pill {{ $application->pill_class }}">{{ $application->display_label }}</span>
                </div>

                <div class="zn-track">
                    <div class="zn-step done"><span>Applied</span></div>
                    <div class="zn-step {{ $isClosed ? '' : 'now' }}"><span>Forms &amp; materials</span></div>
                    <div class="zn-step"><span>Initial interview</span></div>
                    <div class="zn-step"><span>Assessments</span></div>
                </div>

                {{-- The three ways an application ends are told apart, in the
                     applicant's terms: their own decision, a missed deadline, or
                     HR's decision. --}}
                <div class="zn-note">
                    @if (!$isClosed)
                        HR reviews your application once your form and documents are complete, then contacts you
                        to schedule an initial interview.
                        <a class="zn-link" href="{{ route('home') }}">See what's left</a>
                    @else
                        @switch($application->status)
                            @case(\App\Models\Application::WITHDRAWN)
                                @if ($application->closed_by)
                                    <b>Withdrawn.</b> HR withdrew this application for you on {{ $closedOn }}.
                                @else
                                    <b>Withdrawn.</b> You withdrew this application on {{ $closedOn }}.
                                @endif
                                You're welcome to apply for this position again while it's open.
                                @break

                            @case(\App\Models\Application::NON_RESPONSIVE)
                                <b>Closed: no response by the deadline.</b> The documents we asked for weren't all received
                                by the deadline, so this application closed on {{ $closedOn }}.
                                You're welcome to apply for this position again while it's open.
                                @break

                            @case(\App\Models\Application::NOT_SELECTED)
                                <b>Not selected.</b> We won't be moving forward with this application
                                (decided {{ $closedOn }}).
                                @if ($application->reapply_on)
                                    You can apply for this position again on <b>{{ $application->reapply_on->format('F j, Y') }}</b>.
                                @else
                                    You're welcome to apply for this position again while it's open.
                                @endif
                                This doesn't affect any other application.
                                @break

                            @default
                                This application is no longer active.
                        @endswitch
                    @endif
                </div>

                @unless ($isClosed)
                    <div class="zn-application-actions">
                        <button type="button" class="zn-link zn-withdraw-link"
                                data-bs-toggle="modal" data-bs-target="#withdraw-{{ $application->id }}">
                            Withdraw application
                        </button>
                    </div>

                    {{-- One dialog per application, so the one being withdrawn is
                         named everywhere in it, including the box that confirms it. --}}
                    <div class="modal fade" id="withdraw-{{ $application->id }}" tabindex="-1"
                         aria-labelledby="withdraw-{{ $application->id }}-title" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form class="modal-content zn-modal" method="POST"
                                  action="{{ route('applications.withdraw', $application->id) }}">
                                @csrf
                                <div class="modal-header">
                                    <h1 class="modal-title" id="withdraw-{{ $application->id }}-title">
                                        Withdraw your application for {{ $application->job_title }}?
                                    </h1>
                                </div>
                                <div class="modal-body">
                                    <p class="zn-count" style="font-weight:400;margin:0 0 12px">
                                        {{ $application->mr_no }} · Applied {{ $application->applied_at?->format('F j, Y') }}
                                    </p>

                                    <p class="zn-modal-lede" style="margin-bottom:12px">
                                        This ends your application for <b>{{ $application->job_title }}</b> only.
                                        @if ($otherOpen->isNotEmpty())
                                            Your {{ Str::plural('application', $otherOpen->count()) }} for
                                            @foreach ($otherOpen as $other)
                                                <b>{{ $other->job_title }}</b>{{ $loop->remaining > 1 ? ', ' : ($loop->remaining === 1 ? ' and ' : '') }}
                                            @endforeach
                                            {{ $otherOpen->count() === 1 ? 'stays' : 'stay' }} active.
                                        @endif
                                    </p>

                                    <div class="zn-fld">
                                        <label for="withdraw-{{ $application->id }}-note">Reason <span class="zn-opt">optional</span></label>
                                        <input type="text" name="note" id="withdraw-{{ $application->id }}-note" maxlength="500">
                                    </div>

                                    <label class="zn-check">
                                        <input type="checkbox" name="confirm" value="1" required>
                                        <span>Yes, withdraw my application for {{ $application->job_title }}.</span>
                                    </label>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="zn-btn zn-btn-out zn-btn-sm" data-bs-dismiss="modal">Keep application</button>
                                    <button type="submit" class="zn-btn zn-btn-out zn-btn-sm zn-btn-warn">Withdraw application</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endunless
            </div>
        @empty
            <div class="zn-empty">
                <b>You haven't applied to any positions yet</b>
                Browse our open roles — your profile carries over to every application, so you only fill it in once.
                <div class="mt-3">
                    <a class="zn-btn zn-btn-sm" href="{{ route('careers.index') }}">View open positions</a>
                </div>
            </div>
        @endforelse

    </div>
</main>
@endsection
