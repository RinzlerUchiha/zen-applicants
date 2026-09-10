@extends('layouts.assessment')

@section('content')

<style>
    #form-abstract-reasoning {
        font-family: 'Courier New', Courier, monospace;
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
    let duration = 10 * 60; // 10 minutes
    let timeLeft = duration;
    let timer = null;

    $(function() {
        $('#form-abstract-reasoning').submit(async function (e) {
            e.preventDefault();

            try {
                let ans = {};
                $('#form-abstract-reasoning [data-item]').each(function(){
                    const selectedOpt = $('.abstract-reasoning-ans-' + $(this).data('item') + ':checked');
                    ans[$(this).data('item')] = selectedOpt.val();
                });

                const url = @json(route('abstract_reasoning.store'));
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
            $('#timer, #form-abstract-reasoning').show();

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
            $('#form-abstract-reasoning').submit();
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
    <div class="border border-3 border-danger text-danger rounded p-1 position-sticky bg-white" id="timer" style="display: none;">00:10:00</div>
    <form id="form-abstract-reasoning" class="ms-md-5 mb-5" style="{{ !$answer ? 'display: none;' : '' }}" oncontextmenu="return false;">
        <fieldset {{ $answer ? 'disabled' : '' }}>
            <div class="text-muted small mb-3">
                BASIC ABSTRACT REASONING (10 Questions; 10mins exam)
            </div>
            @foreach ($answerList as $i => $item)
                <div class="row mb-2">
                    <div class="col d-flex">
                        <span class="me-3">{{ $i }}</span><img src="{{ $item['question'] }}" class="img-fluid" id="q-{{ $i }}" data-item="{{ $i }}">
                    </div>
                </div>
                <div class="row ps-5 mb-5">
                @foreach ($item['option'] as $o => $opt)
                    <div class="col-auto">
                        <div class="form-check zn-option">
                            <input class="btn-check abstract-reasoning-ans-{{ $i }}" type="radio" value="{{ $o }}" id="opt-{{ $i.'-'.$o }}" name="opt-{{ $i }}" autocomplete="off" {{ ($answer?->abstract_ans[$i] ?? '') == $o ? 'checked' : '' }} required>
                            <label class="zn-btn zn-btn-out" for="opt-{{ $i.'-'.$o }}">{{ $o.')' }} <img src="{{ $opt }}" class="img-fluid rounded"></label>
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