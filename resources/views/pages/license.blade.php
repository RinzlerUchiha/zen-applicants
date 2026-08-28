@extends('layouts.layout')

@section('content')

<style>
    #form-license input,
    #form-license select,
    #license-list {
        font-size: 12px;
    }

    #license-list {
        min-width: 50vw;
        width: fit-content;
    }
</style>

<script type="text/javascript">
    $(function(){
        $('#btn-cancel-edit-license').click(function(){
            $('#form-license input, #form-license select').val('');
            $('#form-license').toggleClass('d-none');
            $('#license-list').toggleClass('d-none');
        });
    });

    function edit_license(e) {
        $('#license-id').val($(e).data('licenseid'));
        $('#license-type').val($(e).data('type'));
        $('#license-registration-date').val($(e).data('registerdate'));
        $('#license-valid-until').val($(e).data('validuntil'));
        $('#license-profession').val($(e).data('profession'));
        $('#license-attachment-current').val($(e).data('attachment'));

        $('#form-license').toggleClass('d-none');
        $('#license-list').toggleClass('d-none');
    }

    async function remove_license(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const url = @json(route('license.delete', ['id' => ':id'])).replace(':id', $(e).data('licenseid'));
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

<div id="license-list">
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
    <table class="table table-sm table-striped table-hover" id="license-list-table">
        <thead>
            <tr>
                <th>License Type</th>
                <th>Registration Date</th>
                <th>Valid Until</th>
                <th>Profession</th>
                <th>Attachment</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($license as $list)
            <tr>
                <td class="text-nowrap">{{ $list->el_type }}</td>
                <td class="text-nowrap">{{ $list->el_regdate }}</td>
                <td class="text-nowrap">{{ $list->el_expdate }}</td>
                <td class="text-nowrap">{{ $list->el_profession }}</td>
                <td>
                    @if($list->el_file)
                        <embed src="{{ '/file/get/license/'.$list->el_file }}" style="max-width: 100%; height: 150px;">
                    @endif
                </td>
                <td>
                    <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1"
                        data-licenseid="{{ $list->el_id }}"
                        data-type="{{ $list->el_type }}"
                        data-registerdate="{{ $list->el_regdate }}"
                        data-validuntil="{{ $list->el_expdate }}"
                        data-profession="{{ $list->el_profession }}"
                        data-attachment="{{ $list->el_file }}"
                        onclick="edit_license(this)">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm m-1" data-licenseid="{{ $list->el_id }}" onclick="remove_license(this)">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_license(this)">Add</button>
</div>

<form id="form-license" enctype="multipart/form-data" name="form-license" method="post" action="{{ route('license.store') }}" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="license-id" id="license-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="license-type" id="license-type">
                <label for="license-type">License Type</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="date" class="form-control-plaintext border-bottom" name="license-registration-date" id="license-registration-date">
                <label for="license-registration-date">Registration Date</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="date" class="form-control-plaintext border-bottom" name="license-valid-until" id="license-valid-until">
                <label for="license-valid-until">Valid Until</label>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="license-profession" id="license-profession">
                <label for="license-profession">Profession</label>
            </div>
        </div>
        
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="file" class="form-control-plaintext border-bottom" name="license-attachment" id="license-attachment">
                <input type="hidden" name="license-attachment-current" id="license-attachment-current">
                <label for="license-attachment">Attachment</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Save</button>
    <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-license">Cancel</button>
</form>

@stop