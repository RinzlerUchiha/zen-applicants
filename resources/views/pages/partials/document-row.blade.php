{{-- One uploaded document. Extracted so required, optional and extra groups
     render identically rather than drifting apart. --}}
<div class="zn-doc">
    <div class="zn-doc-ico">{{ strtoupper(pathinfo($document->doc_file, PATHINFO_EXTENSION)) }}</div>
    <div>
        <div class="zn-doc-name">{{ $document->type_label }}</div>
        <div class="zn-doc-meta">
            {{ $document->doc_original_name }} · {{ $document->size_for_humans }} ·
            {{ $document->uploaded_at?->format('M j, Y') }}
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a class="zn-link" style="font-size:12px" href="{{ route('documents.view', $document->id) }}"
           target="_blank" rel="noopener">View</a>
        <form method="POST" action="{{ route('documents.delete', $document->id) }}" class="m-0"
              onsubmit="return confirm('Remove this document? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="zn-link" style="font-size:12px;color:var(--zn-warn)">Remove</button>
        </form>
    </div>
</div>
