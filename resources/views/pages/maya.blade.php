@extends('layouts.assessment')

@section('content')

    <style>
        #form-maya {
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
            // Every item is sent, unanswered ones as null. The clock is the
            // server's (public/zn-exam.js), not this page's.
            function showMaya(i) {
                const all = $('#form-maya .maya-item');
                all.removeClass('active');
                all.eq(i).addClass('active');
                $('#btn-prev').prop('disabled', i === 0);
                $('#btn-next').prop('disabled', i === all.length - 1);
                $('#btn-submit').toggle(i === all.length - 1);
            }
            function mayaPayload() {
                let ans = {};
                $('#form-maya .maya-item').each(function() {
                    ans[$(this).data('item')] = $(this).find('.maya-opt:checked').val() ?? null;
                });
                return { set: ans };
            }

            $(function() {
                ZnExam.init({
                    payload: mayaPayload,
                    items: () => ZnExam.each(document.querySelectorAll('#form-maya .maya-item'), ZnExam.hasChecked),
                    // One item at a time: the map shows the one chosen.
                    go: (i) => showMaya(i),
                });

                $('#form-maya').submit(function(e) {
                    e.preventDefault();
                    ZnExam.submit();
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
        </script>
    @endif
    <div class="w-100 h-100 position-relative">
        @if (!$answer)
            <form id="form-maya" class="mb-5" oncontextmenu="return false;">
                <fieldset {{ $answer ? 'disabled' : '' }}>
                    <div class="text-muted small mb-3">Maya — choose the piece that completes each pattern. Use Prev and Next to move between items.</div>
                    <div id="maya-list" class="d-flex gap-3" style="width: fit-content;">
                        @if (!$answer)
                            <button class="zn-btn zn-btn-out my-auto" type="button" id="btn-prev"
                                style="height: fit-content;" disabled>Prev</button>
                        @endif

                        @foreach ($answerList as $s => $set)
                            @foreach ($set as $i => $item)
                                <div class="d-flex gap-3 maya-item {{ $loop->parent->index == 0 && $loop->index == 0 ? 'active' : '' }}"
                                    data-item="{{ $s . $i }}">
                                    <div class="d-block">
                                        <span class="mx-auto">Set {{ strtoupper($s . '-' . $i) }}</span>
                                        <img src="{{ route('assessments.image', ['maya', basename($item['question'])]) }}" class="d-block w-auto mx-auto mb-3"
                                            alt="Set {{ strtoupper($s . '-' . $i) }}" draggable="false">
                                    </div>
                                    <div class="d-flex flex-column gap-2 justify-content-center opt-list">
                                        @foreach ($item['options'] as $o)
                                            <input type="radio" class="btn-check maya-opt" name="opt-{{ $s . '-' . $i }}"
                                                id="opt-{{ $s . '-' . $i . '-' . $o }}" 
                                                value="{{ $o }}"
                                                autocomplete="off"
                                                {{ ($prefill[$s . $i] ?? '') == $o ? 'checked' : '' }}>
                                            <label class="zn-btn zn-btn-out"
                                                for="opt-{{ $s . '-' . $i . '-' . $o }}">{{ $o }}</label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endforeach

                        @if (!$answer)
                            <button class="zn-btn zn-btn-out my-auto" type="button" id="btn-next"
                                style="height: fit-content;">Next</button>
                            <button class="zn-btn my-auto ms-3" type="submit" id="btn-submit"
                                style="height: fit-content; display: none;">Submit</button>
                        @endif
                    </div>
                </fieldset>
            </form>
        @else
            <div id="maya-answers" class="d-flex text-nowrap" style="width: fit-content;">
                @foreach ($answerList as $s => $set)
                    <div class="zn-card">
                        <div class="zn-card-subhead">
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
