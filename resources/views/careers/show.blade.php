@extends('layouts.guest')

@section('title', $posting->posting_title)
@section('description', $posting->short_description ?? '')

@section('content')

{{-- A posting opened directly (a shared link, a search result, or JavaScript
     turned off). The careers page shows this same partial beside the list. --}}
<p style="margin:0 0 12px">
    <a class="zn-link" href="{{ route('careers.index') }}">&larr; All positions</a>
</p>

@include('careers.partials.posting')

@include('careers.partials.help-bubble', ['raised' => true])

@endsection
