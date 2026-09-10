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
    let duration = 12 * 60; // 10 minutes
    let timeLeft = duration;
    let timer = null;

    $(function() {
        $('#form-basic-math').submit(async function (e) {
            e.preventDefault();

            try {
                let ans = {};
                $('#form-basic-math [data-item]').each(function(){
                    const selectedOpt = $('.basic-math-ans-' + $(this).data('item') + ':checked');
                    ans[$(this).data('item')] = selectedOpt.val();
                });

                const url = @json(route('basic_math.store'));
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

        $('#btn-start').click(function(){
            $(this).hide();
            $('#timer, #form-basic-math').show();

            if (timer !== null) return; // prevent multiple starts

            updateTimer(); // show first value immediately
            timer = setInterval(updateTimer, 1000);

        });
    });

    function updateTimer() {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;

        seconds = seconds < 10 ? '0' + seconds : seconds;
        $('#timer').text('00:' + (minutes < 10 ? '0' + minutes : minutes) + ':' + seconds);

        if (timeLeft <= 0) {
            $('#form-basic-math').submit();
            alert('Time up!');
            clearInterval(timer);
            timer = null;
            $('#timer').text('Time up!');
            return;
        }

        timeLeft--;
    }
</script>
@endif
<div class="w-100 h-100 position-relative">
    <button class="zn-btn zn-btn-out position-absolute top-0 start-50 translate-middle-x" style="{{ $answer ? 'display: none;' : '' }}" id="btn-start">Start Timer</button>
    <div class="border border-3 border-danger text-danger rounded p-1 position-sticky bg-white" id="timer" style="display: none;">00:12:00</div>

    <form id="form-basic-math" class="ms-md-5 mb-5" style="{{ !$answer ? 'display: none;' : '' }}" oncontextmenu="return false;">
        <fieldset {{ $answer ? 'disabled' : '' }}>
            <div class="text-muted small mb-3">BASIC MATH (12 Questions: 12mins exam)</div>
            @foreach ($answerList as $i => $item)
                <div class="row">
                    <div class="col">
                        <input type="text" readonly tabindex="-1" class="zn-question" id="q-{{ $i }}" data-item="{{ $i }}" value="{{ $item['question'] }}">
                    </div>
                </div>
                @foreach ($item['answer'] as $o => $opt)
                    <div class="row">
                        <div class="col ps-5">
                            <div class="form-check zn-option">
                                <input class="form-check-input basic-math-ans-{{ $i }}" type="radio" data-cat="{{ $o }}" value="{{ $o }}" id="opt-{{ $i.'-'.$o }}" name="opt-{{ $i }}" {{ ($answer?->math_ans[$i] ?? '') == $o ? 'checked' : '' }} required>
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
</div>
@stop