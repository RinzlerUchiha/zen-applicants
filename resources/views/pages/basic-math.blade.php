@extends('layouts.assessment')

@section('content')

<style>
    #form-basic-math {
        font-family: 'Courier New', Courier, monospace;
        user-select: none;
    }

    #form-basic-math .form-check {
        font-size: 15px;
    }

    #form-basic-math fieldset:not(:disabled) .form-check:hover,
    #form-basic-math fieldset:not(:disabled) .form-check-label:hover {
        cursor: pointer;
        font-weight: bold;
    }

    #form-basic-math .form-check-input {
        border: .5px solid gray;
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
    function basicMathPayload() {
        let ans = {};
        $('#form-basic-math [data-item]').each(function(){
            const selectedOpt = $('.basic-math-ans-' + $(this).data('item') + ':checked');
            ans[$(this).data('item')] = selectedOpt.val() ?? null;
        });
        return { set: ans };
    }

    $(function() {
        ZnExam.init({
            payload: basicMathPayload,
            items: () => ZnExam.each(document.querySelectorAll('#form-basic-math [data-item]'),
                (el) => document.querySelector('.basic-math-ans-' + el.dataset.item + ':checked')),
        });
        $('#form-basic-math').submit(function (e) {
            e.preventDefault();
            ZnExam.submit();
        });
    });
</script>
@endif
<div class="w-100 h-100 position-relative">
    <form id="form-basic-math" class="ms-md-5 mb-5" oncontextmenu="return false;">
        <fieldset {{ $answer ? 'disabled' : '' }}>
            <div class="text-muted small mb-3">BASIC MATH ({{ count($answerList) }} questions)</div>
            @foreach ($answerList as $i => $item)
                <div class="row">
                    <div class="col">
                        <input type="text" readonly tabindex="-1" class="zn-question" id="q-{{ $i }}" data-item="{{ $i }}" value="{{ $loop->iteration }}. {{ preg_replace('/^\s*\d+\.\s*/', '', $item['question']) }}">
                    </div>
                </div>
                @foreach ($item['answer'] as $o => $opt)
                    <div class="row">
                        <div class="col ps-5">
                            <div class="form-check zn-option">
                                <input class="form-check-input basic-math-ans-{{ $i }}" type="radio" data-cat="{{ $o }}" value="{{ $o }}" id="opt-{{ $i.'-'.$o }}" name="opt-{{ $i }}" {{ ($prefill[$i] ?? '') == $o ? 'checked' : '' }} required>
                                <label class="form-check-label" for="opt-{{ $i.'-'.$o }}">{{ chr(64 + $loop->iteration) }}. {{ preg_replace('/^\s*[A-D]\.?\s+/', '', $opt) }}</label>
                            </div>
                        </div>
                    </div>
                @endforeach
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