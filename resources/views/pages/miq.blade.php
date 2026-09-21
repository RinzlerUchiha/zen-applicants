@extends('layouts.assessment')

@section('content')

<style>
    #form-miq {
        font-family: 'Courier New', Courier, monospace;
        user-select: none;
    }

    #form-miq .form-check {
        font-size: 17px;
    }

    #form-miq fieldset:not(:disabled) .form-check:hover,
    #form-miq fieldset:not(:disabled) .form-check-label:hover {
        cursor: pointer;
        font-weight: bold;
    }

    #form-miq .form-check-input {
        border: .5px solid gray;
    }
</style>

@if (!$answer)
<script>
    function miqPayload() {
        let ans = {};
        $('.miq-ans:checked').each(function(){
            ans[$(this).data('item')] = {
                cat: $(this).data('cat'),
                ans: this.value
            };
        });
        return { set: ans };
    }

    $(function() {
        ZnExam.init({
            payload: miqPayload,
            // Tick the ones that apply — there is no "unanswered" here.
            progress: () => $('.miq-ans:checked').length + ' ticked',
            check: () => $('.miq-ans:checked').length ? null : 'Tick at least one statement that applies to you.',
        });
        $('#form-miq').submit(function (e) {
            e.preventDefault();
            ZnExam.submit();
        });
    });
</script>
@endif

<form id="form-miq" class="ms-md-5 mb-5" oncontextmenu="return false;">
    <fieldset {{ $answer ? 'disabled' : '' }}>
        <div class="text-muted small mb-3">Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</div>
        @foreach ($answerList as $i => $item)
            <div class="form-check zn-option">
                <input class="form-check-input miq-ans" type="checkbox" data-cat="{{ $item['cat'] }}" value="{{ $item['ans'] }}" data-item="{{ $i }}" id="item-{{ $i }}" {{ in_array($i, $prefill) ? 'checked' : '' }}>
                <label class="form-check-label" for="item-{{ $i }}">{{ $item['ans'] }}</label>
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

@stop