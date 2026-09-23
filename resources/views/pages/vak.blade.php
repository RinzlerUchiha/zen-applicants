@extends('layouts.assessment')

@section('content')

<style>
    #form-vak {
        user-select: none;
    }

    #form-vak .form-check {
        font-size: 15px;
    }

    #form-vak fieldset:not(:disabled) .form-check:hover,
    #form-vak fieldset:not(:disabled) .form-check-label:hover {
        cursor: pointer;
        font-weight: bold;
    }

    #form-vak .form-check-input {
        border: .5px solid gray;
    }
</style>

@if (!$answer)
<script>
    function vakPayload() {
        let ans = {};
        $('#form-vak [data-item]').each(function(){
            const selectedOpt = $('.vak-ans-' + $(this).data('item') + ':checked');
            ans[$(this).data('item')] = selectedOpt.data('cat');
        });
        return { set: ans };
    }

    $(function() {
        ZnExam.init({
            payload: vakPayload,
            items: () => ZnExam.each(document.querySelectorAll('#form-vak [data-item]'),
                (el) => document.querySelector('.vak-ans-' + el.dataset.item + ':checked')),
            requireAll: true,
        });
        $('#form-vak').submit(function (e) {
            e.preventDefault();
            ZnExam.submit();
        });
    });
</script>
@endif

<form id="form-vak" class="mb-5" oncontextmenu="return false;">
    <fieldset {{ $answer ? 'disabled' : '' }}>
        <div class="text-muted small mb-3">Instructions: Choose the the answer that most represents how you generally behave.</div>
        @foreach ($answerList as $i => $item)
            <div class="row">
                <div class="col">
                    <p class="zn-question" tabindex="-1" id="q-{{ $i }}" data-item="{{ $i }}">{{ $item['question'] }}</p>
                </div>
            </div>
            @foreach ($item['answer'] as $o => $opt)
                <div class="row">
                    <div class="col ps-5">
                        <div class="form-check zn-option">
                            <input class="form-check-input vak-ans-{{ $i }}" type="radio" data-cat="{{ $o }}" value="{{ $opt }}" id="opt-{{ $i.'-'.$o }}" name="opt-{{ $i }}" {{ ($prefill[$i] ?? '') == $o ? 'checked' : '' }} required>
                            <label class="form-check-label" for="opt-{{ $i.'-'.$o }}">{{ $opt }}</label>
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

@stop