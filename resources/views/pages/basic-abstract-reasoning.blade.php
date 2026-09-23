@extends('layouts.assessment')

@section('content')

<style>
    #form-abstract-reasoning {
        user-select: none;
    }

    #form-abstract-reasoning .form-check {
        font-size: 15px;
    }

    #form-abstract-reasoning fieldset:not(:disabled) .form-check:hover,
    #form-abstract-reasoning fieldset:not(:disabled) .form-check-label:hover {
        cursor: pointer;
        font-weight: bold;
    }

    #form-abstract-reasoning fieldset:disabled .btn {
        border: none;
    }

    #form-abstract-reasoning .form-check-input {
        border: .5px solid gray;
    }

    img[data-item] {
        height: 70px;
    }

    .form-check img {
        height: 50px;
    }

    #timer {
        width: fit-content;
        top: calc(var(--my-top-space) + 5px);
    }
</style>

@if (!$answer)
<script>
    // Every item is sent, unanswered ones as null. The clock is the server's
    // (public/zn-exam.js), not this page's.
    function abstractPayload() {
        let ans = {};
        $('#form-abstract-reasoning [data-item]').each(function(){
            const selectedOpt = $('.abstract-reasoning-ans-' + $(this).data('item') + ':checked');
            ans[$(this).data('item')] = selectedOpt.val() ?? null;
        });
        return { set: ans };
    }

    $(function() {
        ZnExam.init({
            payload: abstractPayload,
            items: () => ZnExam.each(document.querySelectorAll('#form-abstract-reasoning img[data-item]'),
                (el) => document.querySelector('.abstract-reasoning-ans-' + el.dataset.item + ':checked')),
        });
        $('#form-abstract-reasoning').submit(function (e) {
            e.preventDefault();
            ZnExam.submit();
        });
    });
</script>
@endif
<div class="w-100 h-100 position-relative">
    <form id="form-abstract-reasoning" class="mb-5" oncontextmenu="return false;">
        <fieldset {{ $answer ? 'disabled' : '' }}>
            <div class="text-muted small mb-3">
                BASIC ABSTRACT REASONING ({{ count($answerList) }} questions) — choose the figure that completes each series.
            </div>
            @foreach ($answerList as $i => $item)
                <div class="row mb-2">
                    <div class="col d-flex">
                        <span class="me-3">{{ $loop->iteration }}</span><img src="{{ route('assessments.image', ['abstract_reasoning', basename($item['question'])]) }}" class="img-fluid" id="q-{{ $i }}" data-item="{{ $i }}" alt="Question {{ $loop->iteration }}" draggable="false">
                    </div>
                </div>
                <div class="row ps-5 mb-5">
                @foreach ($item['option'] as $o => $opt)
                    <div class="col-auto">
                        <div class="form-check zn-option">
                            <input class="btn-check abstract-reasoning-ans-{{ $i }}" type="radio" value="{{ $o }}" id="opt-{{ $i.'-'.$o }}" name="opt-{{ $i }}" autocomplete="off" {{ ($prefill[$i] ?? '') == $o ? 'checked' : '' }} required>
                            <label class="zn-btn zn-btn-out" for="opt-{{ $i.'-'.$o }}">{{ chr(96 + $loop->iteration) . ')' }} <img src="{{ route('assessments.image', ['abstract_reasoning', basename($opt)]) }}" class="img-fluid rounded" alt="Choice {{ chr(96 + $loop->iteration) }}" draggable="false"></label>
                        </div>
                    </div>
                @endforeach
                </div>
            @endforeach
        </fieldset>
        @if (!$answer)
            <br>
            <div class="d-flex justify-content-center gap-5">
                <button class="zn-btn" type="submit">Submit</button>
            </div>
        @endif
    </form>
</div>
@stop