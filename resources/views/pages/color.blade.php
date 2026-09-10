@extends('layouts.assessment')

@section('content')

<style>
    #form-color {
        font-family: 'Courier New', Courier, monospace;
        user-select: none;
    }

    #form-color table td label{
        font-size: 12px;
        min-height: 50px;
    }
</style>

@if (!$answer)
<script>
    $(function() {
        $('#form-color').submit(async function (e) {
            e.preventDefault();

            try {
                let ans = {};
                $('.color-ans:checked').each(function(){
                    // ans[$(this).data('item')] = {
                    //     cat: $(this).data('cat'),
                    //     ans: this.value
                    // };
                    ans[$(this).data('item')] = $(this).data('cat');
                });              

                const url = @json(route('color.store'));
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

<form id="form-color" class="ms-md-5 mb-5" oncontextmenu="return false;">
    <fieldset {{ $answer ? 'disabled' : '' }}>
        <div class="text-muted small mb-3">Instructions: Choose the characteristic that best describes you: choose one answer per number.</div>
        <table class="zn-table">
            @foreach ($answerList as $i => $item)
                <tr>
                    <td class="align-middle fs-5">{{ $i }}</td>
                    @foreach ($item as $o => $opt)
                        <td style="height: 10px;">
                            <input type="radio" class="btn-check color-ans" name="opt-{{ $i }}" id="opt-{{ $i.'-'.$o }}" data-cat="{{ $o }}" value="{{ $opt }}" data-item="{{ $i }}" autocomplete="off" {{ ($answer?->wcay_ans[$i] ?? '') == $o ? 'checked' : '' }} required>
                            <label class="zn-btn zn-btn-out d-flex justify-content-center align-items-center h-100 w-100" for="opt-{{ $i.'-'.$o }}">{{ $opt }}</label>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </fieldset>
    @if (!$answer)
        <br>
        <div class="d-flex justify-content-center gap-5">
            <button class="zn-btn" type="submit">Submit</button>
        </div>
    @endif
</form>

@stop