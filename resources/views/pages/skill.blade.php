@extends('layouts.layout')

@section('content')

<style>
    #skills-list {
        min-width: 50vw;
        width: fit-content;
    }
    #skills-list * {
        font-size: 12px;
    }

    #skills-list td:first-child,
    #skills-list td:nth-child(2) {
        width: 40%;
    }

    #form-skill {
        min-width: 50vw;
        width: fit-content;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#skill-category').change(function(){
            $('#skill-type option').not('[value=""]').hide();
            $('#skill-type option[category="'+ this.value +'"]').show();

            if(this.value == '7'){
                $('#skill-other').removeClass('d-none');
                $('#skill-type').addClass('d-none');
            }else{
                $('#skill-other').addClass('d-none');
                $('#skill-type').removeClass('d-none');
            }
        });

        $('#btn-cancel-edit-skill').click(function(){
            $('#form-skill input, #form-skill select').val('');
            $('#form-skill').toggleClass('d-none');
            $('#skills-list').toggleClass('d-none');
        });
    })

    function edit_skill(e) {
        $('#skill-id').val($(e).data('skillid'));
        $('#skill-category').val($(e).data('category'));
        $('#skill-type').val($(e).data('type'));
        $('#skill-other').val($(e).data('other'));

        $('#skill-type option').not('[value=""]').hide();
        $('#skill-type option[category="'+ $('#skill-category').val() +'"]').show();

        if($('#skill-category').val() == '7'){
            $('#skill-other').removeClass('d-none');
            $('#skill-type').addClass('d-none');
        }else{
            $('#skill-other').addClass('d-none');
            $('#skill-type').removeClass('d-none');
        }

        $('#form-skill').toggleClass('d-none');
        $('#skills-list').toggleClass('d-none');
    }

    async function remove_skill(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const url = @json(route('skill.delete', ['id' => ':id'])).replace(':id', $(e).data('skillid'));
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    $(e).closest('tr').remove();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Unable to remove record.');
            }
        }
    }
</script>
<div id="skills-list">
    @if(session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <table class="table table-sm table-striped table-hover">
        <thead>
            <tr>
                <th>Category</th>
                <th>Skills</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($skill as $list)
            <tr>
                <td>{{ $list->sc_title }}</td>
                <td>{{ $list->skill_category == 7 ? $list->skill_others : $list->skill_name }}</td>
                <td class="text-center">
                    <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1"
                        data-skillid="{{ $list->skill_id }}"
                        data-category="{{ $list->skill_category }}"
                        data-type="{{ $list->skill_type }}"
                        data-other="{{ $list->skill_others }}"
                        onclick="edit_skill(this)">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm m-1" data-skillid="{{ $list->skill_id }}" onclick="remove_skill(this)">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_skill(this)">Add</button>
</div>

<form id="form-skill" name="form-skill" method="post" action="{{ route('skill.store') }}" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="skill-id" id="skill-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="skill-category" id="skill-category" aria-label="">
                    <option value="" selected>-Select-</option>
                    @foreach($skillsCategoryList as $sc)
                        <option value="{{ $sc->sc_id }}">{{ $sc->sc_title }}</option>
                    @endforeach
                </select>
                <label for="skill-category">Category</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="skill-type" id="skill-type" aria-label="">
                    <option value="" selected>-Select-</option>
                    @foreach($skillsList as $sl)
                        <option style="display: none;" category="{{ $sl->skil_categID }}" value="{{ $sl->id }}">{{ $sl->skill_name }}</option>
                    @endforeach
                </select>
                <input type="text" name="skill-other" id="skill-other" class="form-control-plaintext border-bottom d-none">
                <label for="skill-type">Type</label>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Save</button>
    <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-skill">Cancel</button>
</form>

@stop