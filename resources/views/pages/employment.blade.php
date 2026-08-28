@extends('layouts.layout')

@section('content')

<style>
    #form-employment input,
    #form-employment select,
    #employment-list {
        font-size: 12px;
    }

    #employment-list {
        min-width: 50vw;
        width: fit-content;
    }
</style>

<script type="text/javascript">
    $(function(){
        $('#btn-cancel-edit-employment').click(function(){
            $('#form-employment input, #form-employment select').val('');
            $('#form-employment').toggleClass('d-none');
            $('#employment-list').toggleClass('d-none');
        });
    });

    function edit_employment(e) {
        $('#employment-id').val($(e).data('employmentid'));
        $('#employment-company').val($(e).data('company'));
        $('#employment-address').val($(e).data('address'));
        $('#employment-position').val($(e).data('position'));
        $('#employment-supervisor').val($(e).data('supervisor'));
        $('#employment-contact').val($(e).data('contact'));
        $('#employment-start-date').val($(e).data('from'));
        $('#employment-end-date').val($(e).data('to'));
        $('#employment-reason').val($(e).data('reason'));

        $('#form-employment').toggleClass('d-none');
        $('#employment-list').toggleClass('d-none');
    }

    async function remove_employment(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const url = @json(route('employment.delete', ['id' => ':id'])).replace(':id', $(e).data('employmentid'));
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

<div id="employment-list">
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
    <table class="table table-sm table-striped table-hover" id="employment-list-table">
        <thead>
            <tr>
                <th>Company</th>
                <th>Address</th>
                <th>Position</th>
                <th>Supervisor</th>
                <th>Contact</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Reason for Leaving</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($employment as $list)
            <tr>
                <td class="text-nowrap">{{ $list->empl_company }}</td>
                <td class="text-nowrap">{{ $list->empl_address }}</td>
                <td class="text-nowrap">{{ $list->empl_position }}</td>
                <td class="text-nowrap">{{ $list->empl_supervisor }}</td>
                <td class="text-nowrap">{{ $list->empl_contact }}</td>
                <td class="text-nowrap">{{ $list->empl_from }}</td>
                <td class="text-nowrap">{{ $list->empl_to }}</td>
                <td class="text-nowrap">{{ $list->empl_reason }}</td>
                <td>
                    <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1"
                        data-employmentid="{{ $list->empl_id }}"
                        data-company="{{ $list->empl_company }}"
                        data-address="{{ $list->empl_address }}"
                        data-position="{{ $list->empl_position }}"
                        data-supervisor="{{ $list->empl_supervisor }}"
                        data-contact="{{ $list->empl_contact }}"
                        data-from="{{ $list->empl_from }}"
                        data-to="{{ $list->empl_to }}"
                        data-reason="{{ $list->empl_reason }}"
                        onclick="edit_employment(this)">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm m-1" data-employmentid="{{ $list->empl_id }}" onclick="remove_employment(this)">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_employment(this)">Add</button>
</div>

<form id="form-employment" name="form-employment" method="post" action="{{ route('employment.store') }}" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="employment-id" id="employment-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="employment-company" id="employment-company">
                <label for="employment-company">Company</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="employment-address" id="employment-address">
                <label for="employment-address">Address</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="employment-position" id="employment-position">
                <label for="employment-position">Position</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="employment-supervisor" id="employment-supervisor">
                <label for="employment-supervisor">Supervisor</label>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="employment-contact" id="employment-contact">
                <label for="employment-contact">Contact</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="date" class="form-control-plaintext border-bottom" name="employment-start-date" id="employment-start-date">
                <label for="employment-start-date">Start Date</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="date" class="form-control-plaintext border-bottom" name="employment-end-date" id="employment-end-date">
                <label for="employment-end-date">End Date</label>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="employment-reason" id="employment-reason">
                <label for="employment-reason">Reason for Leaving</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Save</button>
    <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-employment">Cancel</button>
</form>

@stop