@extends('layouts.assessment')

@section('content')

<style>
    #form-enneagram {
        font-family: 'Courier New', Courier, monospace;
        user-select: none;
    }

    #form-enneagram .form-check {
        font-size: 17px;
    }

    #form-enneagram fieldset:not(:disabled) .form-check:hover,
    #form-enneagram fieldset:not(:disabled) .form-check-label:hover {
        cursor: pointer;
        font-weight: bold;
    }

    #form-enneagram .form-check-input {
        border: .5px solid gray;
    }
</style>

@if (!$answer)
<script>
    $(function() {
        $('#form-enneagram').submit(async function (e) {
            e.preventDefault();

            try {
                let ans = {};
                $('.enneagram-ans:checked').each(function(){
                    if(!ans[$(this).data('set')]){
                        // ans[$(this).data('set')] = {};
                        ans[$(this).data('set')] = [];
                    }

                    // ans[$(this).data('set')][this.value] = $(this).siblings('.form-check-label').text();
                    ans[$(this).data('set')].push(this.value);
                });                

                const url = @json(route('enneagram.store'));
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('[name="csrf-token"]').attr('content'),
                    },
                    body: JSON.stringify({ set: ans })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error.join("\n") || 'Unknown error');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Unable to submit.');
            }
        });
    });
</script>
@endif

<form id="form-enneagram" class="ms-md-5 mb-5" oncontextmenu="return false;">
    <fieldset {{ $answer ? 'disabled' : '' }}>
        <div class="text-muted small mb-3">Instructions: Below are sets of statements. Answer each statement as honestly as you can. Check the statement/s that best describes as you have been throughout most of your life (what you are most of the time).</div>
        @foreach ($answerList as $s => $set)
            <h5 class="text-muted">#{{ $s }}</h5>
            @foreach ($set as $i => $item)
            <div class="form-check zn-option">
                <input class="form-check-input enneagram-ans" type="checkbox" data-set="{{ $s }}" value="{{ $i }}" id="set-{{ $s.'-'.$i }}" {{ in_array($i, ($answer?->enneagram_ans[$s] ?? [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="set-{{ $s.'-'.$i }}">{{ "($i) $item" }}</label>
            </div>
            @endforeach

            @if (!$loop->last)
                <hr>
            @endif
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