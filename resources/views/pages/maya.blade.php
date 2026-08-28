@extends('layouts.layout')

@section('content')

    <style>
        #form-maya {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
        }

        #form-maya .form-check {
            font-size: 15px;
        }

        #form-maya fieldset:not(:disabled) .form-check:hover,
        #form-maya fieldset:not(:disabled) .form-check-label:hover {
            cursor: pointer;
            font-weight: bold;
        }

        #form-maya .form-check-input {
            border: .5px solid gray;
        }

        #timer {
            width: fit-content;
            /* top: calc(var(--my-top-space) + 5px); */
        }

        #maya-list img {
            height: 65vh;
        }

        .maya-item:not(.active) {
            display: none !important;
        }

        .opt-list .btn {
            width: 70px;
        }

        #maya-answers >.card:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #maya-answers >.card:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* .maya-item.active {
                display: flex;
            } */
    </style>

    @if (!$answer)
        <script>
            let duration = 30 * 60; // 10 minutes
            let timeLeft = duration;
            let timer = null;

            $(function() {
                $('#form-maya').submit(async function(e) {
                    e.preventDefault();

                    try {
                        let ans = {};
                        $('#form-maya .maya-item').each(function() {
                            ans[$(this).data('item')] = $(this).find('.maya-opt:checked').val();
                        });

                        const url = @json(route('maya.store'));
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('[name="csrf-token"]').attr('content'),
                            },
                            body: JSON.stringify({
                                set: ans
                            })
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

                $('#btn-start').click(function() {
                    $(this).hide();
                    $('#timer, #form-maya').show();

                    if (timer !== null) return; // prevent multiple starts

                    updateTimer(); // show first value immediately
                    timer = setInterval(updateTimer, 1000);

                });

                $('#btn-prev').click(function() {
                    $('#btn-next').prop('disabled', false);
                    $('#btn-submit').hide();
                    const prev = $('.maya-item.active').prev('.maya-item');
                    $('.maya-item.active').removeClass('active');
                    prev.addClass('active');
                    if (prev.prev('.maya-item').length === 0) {
                        $(this).prop('disabled', true);
                    }
                });

                $('#btn-next').click(function() {
                    $('#btn-prev').prop('disabled', false);
                    const next = $('.maya-item.active').next('.maya-item');
                    $('.maya-item.active').removeClass('active');
                    next.addClass('active');
                    if (next.next('.maya-item').length === 0) {
                        $(this).prop('disabled', true);
                        $('#btn-submit').show();
                    }
                });
            });

            function updateTimer() {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;

                seconds = seconds < 10 ? '0' + seconds : seconds;
                $('#timer').text('00:' + (minutes < 10 ? '0' + minutes : minutes) + ':' + seconds);

                if (timeLeft <= 0) {
                    $('#form-maya').submit();
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
        <button class="btn btn-secondary position-absolute top-0 start-50 translate-middle-x"
            style="{{ $answer ? 'display: none;' : '' }}" id="btn-start">Start Timer</button>
        @if (!$answer)
            <form id="form-maya" class="ms-md-5 mb-5" style="{{ !$answer ? 'display: none;' : '' }}"
                oncontextmenu="/* return false; */">
                <fieldset {{ $answer ? 'disabled' : '' }}>
                    <div class="d-flex mb-3">
                        <div class="border border-3 border-danger text-danger rounded p-1 bg-white" id="timer"
                            style="display: none;">00:30:00</div>
                        <div class="text-muted small ms-3 my-auto">Maya (30mins exam)</div>
                    </div>
                    <div id="maya-list" class="d-flex gap-3" style="width: fit-content;">
                        @if (!$answer)
                            <button class="btn btn-outline-primary my-auto" type="button" id="btn-prev"
                                style="height: fit-content;" disabled>Prev</button>
                        @endif

                        @foreach ($answerList as $s => $set)
                            @foreach ($set as $i => $item)
                                <div class="d-flex gap-3 maya-item {{ $loop->parent->index == 0 && $loop->index == 0 ? 'active' : '' }}"
                                    data-item="{{ $s . $i }}">
                                    <div class="d-block">
                                        <span class="mx-auto">Set {{ strtoupper($s . '-' . $i) }}</span>
                                        <img src="{{ $item['question'] }}" class="d-block w-auto mx-auto mb-3"
                                            alt="...">
                                    </div>
                                    <div class="d-flex flex-column gap-2 justify-content-center opt-list">
                                        @foreach ($item['options'] as $o)
                                            <input type="radio" class="btn-check maya-opt" name="opt-{{ $s . '-' . $i }}"
                                                id="opt-{{ $s . '-' . $i . '-' . $o }}" 
                                                value="{{ $o }}"
                                                autocomplete="off"
                                                {{ ($answer?->maya_ans[$s . $i] ?? '') == $o ? 'checked' : '' }}>
                                            <label class="btn btn-outline-secondary btn-lg"
                                                for="opt-{{ $s . '-' . $i . '-' . $o }}">{{ $o }}</label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endforeach

                        @if (!$answer)
                            <button class="btn btn-outline-primary my-auto" type="button" id="btn-next"
                                style="height: fit-content;">Next</button>
                            <button class="btn btn-primary my-auto ms-3" type="submit" id="btn-submit"
                                style="height: fit-content; display: none;">Submit</button>
                        @endif
                    </div>
                </fieldset>
            </form>
        @else
            <div id="maya-answers" class="d-flex text-nowrap" style="width: fit-content;">
                @foreach ($answerList as $s => $set)
                    <div class="card">
                        <div class="card-header">
                            Set {{ strtoupper($s) }}
                        </div>
                        <ul class="list-group list-group-flush">
                            @foreach ($set as $i => $item)
                                <li class="list-group-item"><small class="text-muted me-3">{{ $i }}.</small> {{ ($answer?->maya_ans[$s . $i] ?? '') }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@stop
