@extends('layouts.assessment')

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

        /* .rank-area.drag-over {
            outline: 2px dashed #444;
            background: #f4f4f4;
        } */

        .rank-area.drag-over { outline: 2px dashed #4CAF50; }
        .rank.dragging { opacity: 0.6; }
    </style>

    @if (!$answer)
        <style>
            #tbl-disc .rank {
                cursor: grab;
            }
        </style>
        <script>
            function initRankDragDropSortable() {
                const $tbl = $('#tbl-disc');

                // Optional: ensure draggable look/feel
                $tbl.find('.rank').css('touch-action', 'none'); // helps reduce scroll interference on mobile

                // Create one Sortable per drop-zone (.rank-area)
                $tbl.find('.rank-area').each(function () {
                    const areaEl = this;

                    new Sortable(areaEl, {
                    group: { name: 'ranks', pull: true, put: true },
                    draggable: '.rank',
                    animation: 150,

                    // ✅ Mobile reliability (iOS/Android)
                    forceFallback: true,
                    fallbackOnBody: true,
                    fallbackTolerance: 3,

                    // visuals
                    ghostClass: 'dragging',

                    // Equivalent to your "only allow drop if canSwap(...)"
                    onMove: function (evt) {
                        const $dragged = $(evt.dragged);

                        // The target area is evt.to (a .rank-area)
                        const $targetArea = $(evt.to);

                        // The rank currently in the target area (excluding the dragged element if already there)
                        const $targetRank = $targetArea.find('.rank').not(evt.dragged);

                        // If there is no targetRank (temporary empty during drag), allow move
                        if ($targetRank.length === 0) return true;

                        // Your original rule gate
                        return !!canSwap($dragged, $targetRank);
                    },

                    // Highlight like your dragenter/leave
                    onChange: function (evt) {
                        $('.rank-area').removeClass('drag-over');
                        $(evt.to).addClass('drag-over');
                    },

                    onChoose: function () {
                        // start drag
                        $('.rank-area').removeClass('drag-over');
                    },

                    onUnchoose: function () {
                        // cleanup highlight
                        $('.rank-area').removeClass('drag-over');
                    },

                    // This is where we preserve your "swap values by swapping DOM nodes"
                    onAdd: function (evt) {
                        const toEl = evt.to;     // destination .rank-area
                        const fromEl = evt.from; // source .rank-area
                        const itemEl = evt.item; // dragged .rank

                        const $to = $(toEl);
                        const $from = $(fromEl);

                        // If destination already had a rank, move that one back to source (swap)
                        const $otherInTo = $to.find('.rank').not(itemEl);
                        if ($otherInTo.length) {
                        $from.append($otherInTo.first());
                        }

                        // remove highlight
                        $('.rank-area').removeClass('drag-over');
                    },

                    onEnd: function () {
                        // final cleanup (like your dragend)
                        $('.rank-area').removeClass('drag-over');
                    }
                    });
                });
            }

            $(function() {
                initRankDragDropSortable();

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
            <table class="zn-table" id="tbl-disc">
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
                <button class="zn-btn" id="btn-submit">Submit</button>
            </div>
        @endif
    </div>

@stop
