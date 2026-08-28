@extends('layouts.layout')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script> --}}

    {{-- <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script> --}}
    {{-- <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css"> --}}
    <style>
        #form-disc {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
        }

        #tbl-disc .rank {
            display: inline-block;
            width: 50px;
            height: 35px;
            border: 1px solid #ccc;
            user-select: none;
            border-radius: 5px;
            text-align: center;
            vertical-align: middle;
            line-height: 35px;
        }

        .rank-area.drag-over {
            outline: 2px dashed #444;
            background: #f4f4f4;
        }
    </style>

    @if (!$answer)
        <style>
            #tbl-disc .rank {
                cursor: grab;
            }
        </style>
        <script>
            $(function() {
                let $dragged = null;

                // Make the .rank draggable
                $('#tbl-disc').find('.rank').attr('draggable', 'true');

                // Drag start
                $('#tbl-disc').on('dragstart', '.rank', function(e) {
                    $dragged = $(this);
                    e.originalEvent.dataTransfer.setData('text/plain', 'rank');
                    e.originalEvent.dataTransfer.effectAllowed = 'move';
                });

                // Allow drop on .rank-area only
                $('#tbl-disc').on('dragover', '.rank-area', function(e) {
                    if (!$dragged) return;

                    const $targetRank = $(this).find('.rank');
                    if (!canSwap($dragged, $targetRank)) return;

                    e.preventDefault(); // allow drop
                });

                // Highlight the area on dragenter
                $('#tbl-disc').on('dragenter', '.rank-area', function(e) {
                    if (!$dragged) return;

                    const $targetRank = $(this).find('.rank');
                    if (!canSwap($dragged, $targetRank)) return;

                    $(this).addClass('drag-over');
                    e.preventDefault();
                });

                // Remove highlight when leaving
                $('#tbl-disc').on('dragleave', '.rank-area', function(e) {
                    if (!this.contains(e.relatedTarget)) {
                        $(this).removeClass('drag-over');
                    }
                });

                // Drop operation: swap values
                $('#tbl-disc').on('drop', '.rank-area', function(e) {
                    e.preventDefault();

                    $(this).removeClass('drag-over');

                    const $targetRank = $(this).find('.rank');

                    if (!$dragged || !canSwap($dragged, $targetRank)) return;

                    // Swap numbers
                    // const temp = $dragged.text();
                    // $dragged.text($targetRank.text());
                    // $targetRank.text(temp);

                    const $draggedParent = $dragged.parent();
                    $dragged.detach().appendTo($targetRank.parent());
                    $targetRank.detach().appendTo($draggedParent);

                    $dragged = null;
                });

                // Clean reset
                $('#tbl-disc').on('dragend', '.rank', function() {
                    $dragged = null;
                    $('.rank-area').removeClass('drag-over');
                });

                $('#btn-submit').click(async function () {
                    try {
                        let ans = {};
                        $('[data-set]').each(function(){
                            if(!ans[$(this).data('set')]){
                                ans[$(this).data('set')] = {};
                            }

                            // ans[$(this).data('set')][$(this).data('item')] = { rank: $(this).find('.rank').data('rank'), content: $(this).data('content') };
                            ans[$(this).data('set')][$(this).data('item')] = $(this).find('.rank').data('rank');
                        });

                        const url = @json(route('disc.store'));
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

            // Helper: ensure same data-set
            function canSwap($aRank, $bRank) {
                if (!$aRank || !$bRank) return false;

                const setA = $aRank.closest('tr').data('set');
                const setB = $bRank.closest('tr').data('set');

                return setA && setA === setB;
            }
        </script>
    @endif

    <div id="form-disc" class="mx-md-5 mb-5" oncontextmenu="return false;">
        <fieldset {{ $answer ? 'disabled' : '' }}>
            <div class="text-muted small mb-3">Instrucions: Rank each category of words on a scale of 4,3,2,1 with 4 being the word that best describes you and 1 being the least like you. Use all rankings in each category only once.</div>
            <table class="table" id="tbl-disc">
                <tbody>
                    @foreach ($answerList as $s => $set)
                        <tr>
                            <th>Rank</th>
                            <th>Set {{ $s }}</th>
                        </tr>
                        @foreach ($set as $i => $item)
                            <tr data-set="{{ $s }}" data-item="{{ $i }}" data-content="{{ $item }}">
                                <td class="rank-area">
                                    <div class="rank" data-rank="{{ $answer?->disc_ans[$s][$i] ?? $loop->iteration }}">{{ $answer?->disc_ans[$s][$i] ?? $loop->iteration }}</div>
                                </td>
                                <td>{{ $item }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </fieldset>
        @if (!$answer)
            <br>
            <div class="d-flex justify-content-center gap-5">
                <button class="btn btn-primary" id="btn-submit">Submit</button>
            </div>
        @endif
    </div>

@stop
