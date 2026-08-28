@extends('layouts.layout')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <style>
        #form-career-anchors {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
        }

        #tbl-career-anchors .item-rate {
            width: 50px;
            /* height: 35px; */
        }

        #tbl-career-anchors .chk-highest {
            font-size: 20px;
        }
    </style>

    @if (!$answer)
        <script>
            let target;
            $(function() {
                $('#tbl-career-anchors input.item-rate').on('input', function(){
                    $('.chk-highest').hide();
                    $('.chk-highest').prop('checked', false);
                    $('.chk-highest').prop('disabled', false);
                    if(this.value > 6) this.value = '';
                    if($('input.item-rate').filter((i, el) => $.trim(el.value).length === 0).length === 0){
                        const sortedDesc = $('input.item-rate')
                            .toArray() // get plain array of DOM elements
                            .sort((a, b) => b.value - a.value); // sort descending by value                           

                        let counter = 0;
                        let prevscore = 0;
                        sortedDesc.forEach((el, i) => {
                            if (counter < 3 || el.value == prevscore) {
                                $(el).closest('tr').find('.chk-highest').show();
                                if(counter < 3 && !((i == 0 || i == 1 || i == 2) && sortedDesc[3].value == el.value)){
                                    $(el).closest('tr').find('.chk-highest').prop('checked', true);
                                    $(el).closest('tr').find('.chk-highest').prop('disabled', true);
                                }
                                counter++;
                                prevscore = el.value;
                            }
                        });
                    }
                });

                $('.chk-highest').change(function(){
                    if($('.chk-highest:checked').length >= 3){
                        $('.chk-highest:visible').not(':checked').prop('disabled', true);
                        $('.chk-highest:visible').not(':checked').prop('checked', false);
                    }else{
                        $('.chk-highest:visible').not(':checked').prop('disabled', false);
                    }
                });
                
                $('#form-career-anchors').submit(async function (e) {
                    e.preventDefault();
                    try {
                        let ans = {};
                        let highest = {};
                        $('tr[data-item]').each(function(){
                            ans[$(this).data('item')] = parseInt($(this).find('.item-rate').val());
                            if($(this).find('.chk-highest').is(':checked')){
                                highest[$(this).data('item')] = parseInt($(this).find('.item-rate').val()) + 4;
                                ans[$(this).data('item')] += 4;
                            }
                        });

                        if(Object.keys(highest).length < 3){
                            alert('Please check the 3 highest items that seem most true for you');
                            return;
                        }

                        const url = @json(route('career_anchors.store'));
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('[name="csrf-token"]').attr('content'),
                            },
                            body: JSON.stringify({ set: ans, highest: highest })
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

    <form id="form-career-anchors" class="mx-md-5 mb-5" oncontextmenu="return false;">
        <fieldset {{ $answer ? 'disabled' : '' }}>
            <div class="text-muted small mb-3">Use the following scale to rate how each of the items is for you. Check the THREE highest items that seem most true for you</div>
            <table class="table text-center">
                <tr>
                    <th>Never True for Me</th>
                    <th colspan="2">Occasionally True for Me</th>
                    <th colspan="2">Often True for Me</th>
                    <th>Always True for Me</th>
                    <th rowspan="2"></th>
                </tr>
                <tr>
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                    <th>6</th>
                </tr>
            </table>
            <table class="table" id="tbl-career-anchors">
                <tbody>
                    @foreach ($answerList as $i => $item)
                        <tr data-item="{{ $i }}">
                            <td>
                                <input class="form-check-input chk-highest border border-dark" type="checkbox" value="checked" id="item-{{ $i }}-highest" style="{{ empty($answer?->career_highest[$i]) ? 'display: none;' : '' }}" {{ !empty($answer?->career_highest[$i]) ? 'checked' : '' }}>
                            </td>
                            <td>
                                <input type="number" class="item-rate" id="item-{{ $i }}" min="1" max="6" value="{{ $answer?->career_ans[$i] }}">
                                {{-- <select class="item-rate" id="item-{{ $i }}">
                                    <option value="1" {{ $answer?->career_ans[$i] == 1 ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ $answer?->career_ans[$i] == 2 ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ $answer?->career_ans[$i] == 3 ? 'selected' : '' }}>3</option>
                                    <option value="4" {{ $answer?->career_ans[$i] == 4 ? 'selected' : '' }}>4</option>
                                    <option value="5" {{ $answer?->career_ans[$i] == 5 ? 'selected' : '' }}>5</option>
                                    <option value="6" {{ $answer?->career_ans[$i] == 6 ? 'selected' : '' }}>6</option>
                                    @if ($answer?->career_ans[$i] > 6)
                                        <option value="{{ $answer?->career_ans[$i] }}" selected disabled>{{ $answer?->career_ans[$i] }}</option>
                                    @endif
                                </select> --}}
                            </td>
                            <td>{{ $item }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </fieldset>
        @if (!$answer)
            <br>
            <div class="d-flex justify-content-center gap-5">
                <button type="submit" class="btn btn-primary" id="btn-submit">Submit</button>
            </div>
        @endif
    </form>
@stop
