{{-- One application-stage document type: what is on file, HR's check of it,
     and anything HR has asked for. Required and optional render identically. --}}
@php
    $document = $slot['document'];
    $request = $slot['request'];
    $rejected = $document?->review_status === 'rejected';
    $openRequest = $request && $request->status === 'open';

    $pillClass = [
        'pending' => 'zn-pill-opt',
        'accepted' => 'zn-pill-ok',
        'rejected' => 'zn-pill-req',
    ][$document?->review_status] ?? 'zn-pill-opt';
@endphp

@if ($document)
    <div class="zn-doc {{ $rejected ? 'needs-action' : '' }}">
        <div class="zn-doc-ico">{{ $document->extension }}</div>
        <div>
            <div class="zn-doc-name">
                {{ $slot['label'] }}
                <span class="zn-pill {{ $pillClass }}">{{ $document->review_label }}</span>
            </div>
            <div class="zn-doc-meta">
                {{ $document->doc_original_name }} · {{ $document->size_for_humans }} ·
                sent {{ $document->uploaded_at?->format('M j, Y') }}
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a class="zn-link" style="font-size:12px" href="{{ route('documents.view', $document->id) }}"
               target="_blank" rel="noopener">View</a>
            <button type="button" class="{{ $rejected ? 'zn-btn zn-btn-sm' : 'zn-link' }} js-pick"
                    style="{{ $rejected ? '' : 'font-size:12px' }}" data-type="{{ $slot['type'] }}">Replace</button>
        </div>

        @if ($rejected)
            <div class="zn-doc-review">
                <b>Why it needs replacing</b>
                <span>{{ $document->review_reason_text }}</span>
                @if ($document->review_note)
                    <span class="zn-doc-review-note">HR: “{{ $document->review_note }}”</span>
                @endif
            </div>
        @endif
    </div>
@else
    <div class="zn-doc {{ $slot['required'] || $openRequest ? 'required-missing' : 'missing' }}">
        <div class="zn-doc-ico">{!! $slot['required'] || $openRequest ? '<i class="bi bi-exclamation-lg"></i>' : '—' !!}</div>
        <div>
            <div class="zn-doc-name">{{ $slot['label'] }}</div>
            <div class="zn-doc-meta">
                @if ($openRequest)
                    HR has asked for this{{ $slot['request_for'] ? ' for ' . $slot['request_for'] : '' }}
                @elseif ($slot['required'])
                    Still needed to complete your application
                @else
                    Not required, but it helps your application stand out
                @endif
            </div>
        </div>
        <button type="button" class="{{ $slot['required'] || $openRequest ? 'zn-btn zn-btn-sm' : 'zn-link' }} js-pick"
                data-type="{{ $slot['type'] }}">Upload</button>

        @if ($openRequest && $request->note)
            <div class="zn-doc-review">
                <span class="zn-doc-review-note">HR: “{{ $request->note }}”</span>
            </div>
        @endif
    </div>
@endif
