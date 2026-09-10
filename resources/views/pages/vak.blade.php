@extends('layouts.assessment')

@section('content')

<style>
    #form-vak {
        font-family: 'Courier New', Courier, monospace;
        user-select: none;
    }

    #form-vak .form-check {
        font-size: 15px;
    }

    #form-vak fieldset:not(:disabled) .form-check:hover,
    #form-vak fieldset:not(:disabled) .form-check-label:hover {
        cursor: pointer;
        font-weight: bold;
    }

    #form-vak .form-check-input {
        border: .5px solid gray;
    }
</style>

@if (!$answer)
<script>
    $(function() {
        $('#form-vak').submit(async function (e) {
            e.preventDefault();

            try {
                let ans = {};
                $('#form-vak [data-item]').each(function(){
                    const selectedOpt = $('.vak-ans-' + $(this).data('item') + ':checked');
                    // ans[$(this).data('item')] = {
                    //     q: this.value,
                    //     cat: selectedOpt.data('cat'),
                    //     ans: selectedOpt.val()
                    // };

                    ans[$(this).data('item')] = selectedOpt.data('cat');
                });

                const url = @json(route('vak.store'));
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

<form id="form-vak" class="ms-md-5 mb-5" oncontextmenu="return false;">
    <fieldset {{ $answer ? 'disabled' : '' }}>
        <div class="text-muted small mb-3">Instructions: Choose the the answer that most represents how you generally behave.</div>
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
                            <input class="form-check-input vak-ans-{{ $i }}" type="radio" data-cat="{{ $o }}" value="{{ $opt }}" id="opt-{{ $i.'-'.$o }}" name="opt-{{ $i }}" {{ ($answer?->vak_ans[$i] ?? '') == $o ? 'checked' : '' }} required>
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

@stop