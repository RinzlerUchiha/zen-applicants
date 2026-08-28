@extends('layouts.layout')

@section('content')

<style>
    #form-miq {
        font-family: 'Courier New', Courier, monospace;
        user-select: none;
    }

    #form-miq .form-check {
        font-size: 17px;
    }

    #form-miq fieldset:not(:disabled) .form-check:hover,
    #form-miq fieldset:not(:disabled) .form-check-label:hover {
        cursor: pointer;
        font-weight: bold;
    }

    #form-miq .form-check-input {
        border: .5px solid gray;
    }
</style>

@if (!$answer)
<script>
    $(function() {
        $('#form-miq').submit(async function (e) {
            e.preventDefault();

            try {
                let ans = {};
                $('.miq-ans:checked').each(function(){
                    ans[$(this).data('item')] = {
                        cat: $(this).data('cat'),
                        ans: this.value
                    };
                });              

                const url = @json(route('miq.store'));
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

<form id="form-miq" class="ms-md-5 mb-5" oncontextmenu="return false;">
    <fieldset {{ $answer ? 'disabled' : '' }}>
        <div class="text-muted small mb-3">Research Shows that all human beings have at least eight different types of intelligences. Depending on your background and age, some intelligences are more developed than the others. This activity will help you find out what your strengths are. Knowing this, you can strengthen the other intelligences that you do not use as often.</div>
        @foreach ($answerList as $i => $item)
            <div class="form-check">
                <input class="form-check-input miq-ans" type="checkbox" data-cat="{{ $item['cat'] }}" value="{{ $item['ans'] }}" data-item="{{ $i }}" id="item-{{ $i }}" {{ in_array($i, ($answer?->miq_ans ?? [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="item-{{ $i }}">{{ $item['ans'] }}</label>
            </div>
        @endforeach
    </fieldset>
    @if (!$answer)
        <br>
        <div class="d-flex justify-content-center gap-5">
            <button class="btn btn-primary" type="submit">Submit</button>
        </div>
    @endif
</form>

@stop