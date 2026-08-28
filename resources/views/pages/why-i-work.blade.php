@extends('layouts.layout')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <style>
        #form-why-i-work {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
        }

        #tbl-why-i-work .rank {
            width: 50px;
            /* height: 35px; */
        }
    </style>

    @if (!$answer)
        <style>
            #tbl-why-i-work .rank {
                cursor: pointer;
            }
        </style>
        <script>
            let target;
            $(function() {

                $('#optionModal').on('show.bs.modal', function(e) {
                    target = $(e.relatedTarget);
                });

                $('.btn-rank-opt').click(function(){
                    const src = $('.rank').filter((_, el) => el.value === this.value);
                    src.val(target.val());
                    src.text(target.val());

                    target.val(this.value);
                    target.text(this.value);

                    $('#optionModal').modal('hide');
                });
                
                $('#btn-submit').click(async function () {
                    try {
                        let ans = {};
                        $('tr[data-item]').each(function(){
                            ans[$(this).data('item')] = $(this).find('.rank').val();
                        });

                        const url = @json(route('why_i_work.store'));
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

    <div id="form-why-i-work" class="mx-md-5 mb-5" oncontextmenu="return false;">
        <fieldset {{ $answer ? 'disabled' : '' }}>
            <div class="text-muted small mb-3">Below is a list of outcomes that a person might receive for working. Read through all the outcomes and then rank them from 1 to 12 in order of importance (the most important outcome would be ranked 1)</div>
            <table class="table" id="tbl-why-i-work">
                <tbody>
                    <tr>
                        <th>Rank</th>
                        <th>Outcome</th>
                        <th>Description</th>
                    </tr>
                    @foreach ($answerList as $i => $item)
                        <tr data-item="{{ $i }}">
                            <td>
                                <button id="item-{{ $i }}" type="button" class="btn btn-outline-secondary rank" data-bs-toggle="modal" data-bs-target="#optionModal" value="{{ $answer?->{'outcome_'.$i} ?? $loop->iteration }}">{{ $answer?->{'outcome_'.$i} ?? $loop->iteration }}</button>
                            </td>
                            <td>{{ $item['cat'] }}</td>
                            <td>{{ $item['desc'] }}</td>
                        </tr>
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

    <div class="modal fade" id="optionModal" tabindex="-1" aria-labelledby="optionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-6" id="optionModalLabel">Select Rank</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row row-cols-3 g-1 mb-3">
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="1">1</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="2">2</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="3">3</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="4">4</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="5">5</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="6">6</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="7">7</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="8">8</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="9">9</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="10">10</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="11">11</button></div>
                            <div class="col"><button class="btn btn-light btn-rank-opt w-100" value="12">12</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
