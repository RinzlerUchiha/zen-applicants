@extends('layouts.layout')

@section('content')

<style>
    #form-characterref input,
    #form-characterref select,
    #characterref-list {
        font-size: 12px;
    }

    #characterref-list {
        min-width: 50vw;
        width: fit-content;
    }
</style>

<script type="text/javascript">
    $(function(){
        $('#btn-cancel-edit-characterref').click(function(){
            $('#form-characterref input, #form-characterref select').val('');
            $('#form-characterref').toggleClass('d-none');
            $('#characterref-list').toggleClass('d-none');
        });
    });

    function edit_characterref(e) {
        $('#characterref-id').val($(e).data('characterrefid'));
        $('#characterref-name').val($(e).data('fullname'));
        $('#characterref-company').val($(e).data('company'));
        $('#characterref-address').val($(e).data('address'));
        $('#characterref-position').val($(e).data('position'));
        $('#characterref-contact').val($(e).data('contact'));
        $('#characterref-relationship').val($(e).data('relationship'));

        $('#form-characterref').toggleClass('d-none');
        $('#characterref-list').toggleClass('d-none');
    }

    async function remove_characterref(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const url = @json(route('characterref.delete', ['id' => ':id'])).replace(':id', $(e).data('characterrefid'));
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

<div id="characterref-list">
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
    <table class="table table-sm table-striped table-hover" id="characterref-list-table">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Company</th>
                <th>Address</th>
                <th>Position</th>
                <th>Contact</th>
                <th>Relationship</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($characterref as $list)
            <tr>
                <td class="text-nowrap">{{ $list->ref_fullname }}</td>
                <td class="text-nowrap">{{ $list->ref_company }}</td>
                <td class="text-nowrap">{{ $list->ref_address }}</td>
                <td class="text-nowrap">{{ $list->ref_position }}</td>
                <td class="text-nowrap">{{ $list->ref_contact }}</td>
                <td class="text-nowrap">{{ $list->ref_relationship }}</td>
                <td>
                    <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1"
                        data-characterrefid="{{ $list->ref_id }}"
                        data-fullname="{{ $list->ref_fullname }}"
                        data-company="{{ $list->ref_company }}"
                        data-address="{{ $list->ref_address }}"
                        data-position="{{ $list->ref_position }}"
                        data-contact="{{ $list->ref_contact }}"
                        data-relationship="{{ $list->ref_relationship }}"
                        onclick="edit_characterref(this)">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm m-1" data-characterrefid="{{ $list->ref_id }}" onclick="remove_characterref(this)">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_characterref(this)">Add</button>
</div>

<form id="form-characterref" name="form-characterref" method="post" action="{{ route('characterref.store') }}" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="characterref-id" id="characterref-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="characterref-name" id="characterref-name">
                <label for="characterref-name">Full Name</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="characterref-company" id="characterref-company">
                <label for="characterref-company">Company</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="characterref-address" id="characterref-address">
                <label for="characterref-address">Address</label>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="characterref-position" id="characterref-position">
                <label for="characterref-position">Position</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="characterref-contact" id="characterref-contact">
                <label for="characterref-contact">Contact</label>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="characterref-relationship" id="characterref-relationship">
                <label for="characterref-relationship">Relationship</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Save</button>
    <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-characterref">Cancel</button>
</form>

@stop