@extends('layouts.assessment')

@section('content')

<style>
    /* The option cells wrap instead of forcing one wide row (they used to
       run past the card, and off-screen entirely on a phone). */
    #form-color table,
    #form-color tbody {
        display: block;
    }

    #form-color tr {
        display: grid;
        grid-template-columns: 40px repeat(auto-fit, minmax(160px, 1fr));
        gap: 8px;
        align-items: stretch;
        padding: 6px 0;
        border-bottom: 1px solid var(--zn-line);
    }

    #form-color td {
        display: block;
        height: auto !important;
    }

    #form-color td .zn-btn {
        min-height: 40px;
        white-space: normal;
    }

    /* On a phone there is room for one choice at a time: the number leads the
       group, then its four options, each full width. */
    @media (max-width: 599.98px) {
        #form-color tr {
            grid-template-columns: 1fr;
            gap: 6px;
        }

        #form-color td.align-middle {
            font-weight: 700;
        }
    }

    #form-color {
        user-select: none;
    }

    #form-color table td label{
        font-size: 12px;
        min-height: 50px;
    }
</style>

@if (!$answer)
<script>
    function colorPayload() {
        let ans = {};
        $('.color-ans:checked').each(function(){
            ans[$(this).data('item')] = $(this).data('cat');
        });
        return { set: ans };
    }

    $(function() {
        ZnExam.init({
            payload: colorPayload,
            items: () => ZnExam.each(document.querySelectorAll('#form-color tr'), ZnExam.hasChecked),
            requireAll: true,
        });
        $('#form-color').submit(function (e) {
            e.preventDefault();
            ZnExam.submit();
        });
    });
</script>
@endif

<form id="form-color" class="mb-5" oncontextmenu="return false;">
    <fieldset {{ $answer ? 'disabled' : '' }}>
        <div class="text-muted small mb-3">Instructions: Choose the characteristic that best describes you: choose one answer per number.</div>
        <table class="zn-table">
            @foreach ($answerList as $i => $item)
                <tr>
                    <td class="align-middle fs-5">{{ $i }}</td>
                    @foreach ($item as $o => $opt)
                        <td style="height: 10px;">
                            <input type="radio" class="btn-check color-ans" name="opt-{{ $i }}" id="opt-{{ $i.'-'.$o }}" data-cat="{{ $o }}" value="{{ $opt }}" data-item="{{ $i }}" autocomplete="off" {{ ($prefill[$i] ?? '') == $o ? 'checked' : '' }} required>
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