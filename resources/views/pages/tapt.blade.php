@extends('layouts.layout')

@section('content')

<style>
    #form-tapt {
        font-family: 'Courier New', Courier, monospace;
        user-select: none;
    }

    #form-tapt .form-check {
        font-size: 15px;
    }

    #form-tapt fieldset:not(:disabled) .set-row:hover,
    #form-tapt fieldset:not(:disabled) .set-row .form-check-label:hover {
        cursor: pointer;
        font-weight: bold;
    }

    #form-tapt .form-check-input {
        border: .5px solid gray;
    }
</style>

@if (!$answer)
<script>
    $(function() {
        $('#form-tapt').submit(async function (e) {
            e.preventDefault();

            try {
                let ans = {};
                $('.tapt-ans:checked').each(function(){
                    if(!ans[$(this).data('set')]){
                        ans[$(this).data('set')] = {};
                    }

                    // ans[$(this).data('set')][$(this).data('row')] = [this.value, $(this).siblings('.form-check-label').text()];
                    ans[$(this).data('set')][$(this).data('row')] = this.value;
                });

                const url = @json(route('tapt.store'));
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

<form id="form-tapt" class="mx-md-5 mb-5" oncontextmenu="return false;">
    <fieldset {{ $answer ? 'disabled' : '' }}>
        <div class="text-muted small mb-3">Instructions: Below are four set of word pairs. Review each pair carefully. Choose the ONE word in each pair which most accurately describes the “real you” by putting a check mark before each word. Remember that there are no right or wrong responses</div>
        <div class="row g-3">
            @foreach ($answerList as $s => $set)
                <div class="col-md-6">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-around">
                            <h5>{{ !empty($set[1]) ? strtoupper(array_keys($set[1])[0]) : '' }}</h5>
                            <h5>or</h5>
                            <h5>{{ !empty($set[1]) ? strtoupper(array_keys($set[1])[1]) : '' }}</h5>
                        </div>
                        @foreach ($set as $i => $item)
                            <div class="d-flex justify-content-evenly set-row border-top border-3">
                                @foreach ($item as $o => $opt)
                                    <div class="form-check my-0 py-1 w-100 {{ $loop->last ? 'ms-5' : '' }}">
                                        <input class="form-check-input tapt-ans" type="radio" data-set="{{ $s }}" data-row="{{ $i }}" value="{{ $o }}" name="set-{{ $s.'-'.$i }}" id="set-{{ $s.'-'.$i.'-'.$o }}" {{ ($answer?->tapt_ans[$s][$i] ?? '') == $o ? 'checked' : '' }} required>
                                        <label class="form-check-label w-100" for="set-{{ $s.'-'.$i.'-'.$o }}">{{ $opt }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </fieldset>
    @if (!$answer)
        <br>
        <div class="d-flex justify-content-center gap-5">
            <button class="btn btn-primary" type="submit">Submit</button>
        </div>
    @endif
    <br>
    <small class="text-muted mb-3">This exam was adopted from Paul D. Tiger & Barbara Barron-Tieger’s book on Do What You Are. Copyright 1992</small>
</form>

@stop