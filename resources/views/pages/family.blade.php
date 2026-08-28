@extends('layouts.layout')

@section('content')

<style>
    #form-family input,
    #form-family select,
    #family-list {
        font-size: 12px;
    }

    #family-list {
        min-width: 50vw;
        width: fit-content;
    }
</style>

<script type="text/javascript">
    $(function(){
        $('#btn-cancel-edit-family').click(function(){
            $('#form-family input, #form-family select').val('');
            $('#form-family').toggleClass('d-none');
            $('#family-list').toggleClass('d-none');
        });
    });

    function edit_family(e) {
        $('#family-id').val($(e).data('famid'));
        $('#family-relationship').val($(e).data('relationship'));
        $('#family-firstname').val($(e).data('firstname'));
        $('#family-middlename').val($(e).data('middlename'));
        $('#family-lastname').val($(e).data('lastname'));
        $('#family-suffix').val($(e).data('suffix'));
        $('#family-maidenname').val($(e).data('maidenname'));
        $('#family-birthdate').val($(e).data('birthdate'));
        $('#family-sex').val($(e).data('sex'));
        $('#family-contact').val($(e).data('contact'));
        $('#family-address').val($(e).data('address'));
        $('#family-occupation').val($(e).data('occupation'));
        $('#family-workplace').val($(e).data('workplace'));

        $('#form-family').toggleClass('d-none');
        $('#family-list').toggleClass('d-none');
    }

    async function remove_family(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const url = @json(route('family.delete', ['id' => ':id'])).replace(':id', $(e).data('famid'));
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('[name="csrf-token"]').attr('content'),
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

<div id="family-list">
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
    <table class="table table-sm table-striped table-hover" id="family-list-table">
        <thead>
            <tr>
                <th>Relationship</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Suffix</th>
                <th>Maiden Name</th>
                <th>Birth Date</th>
                <th>Age</th>
                <th>Sex</th>
                <th>Contact #</th>
                <th>Address</th>
                <th>Occupation</th>
                <th>Work Address</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($family as $list)
            <tr>
                <td class="text-nowrap">{{ $list->fam_relationship }}</td>
                <td class="text-nowrap">{{ $list->fam_lastname }}</td>
                <td class="text-nowrap">{{ $list->fam_firstname }}</td>
                <td class="text-nowrap">{{ $list->fam_midname }}</td>
                <td class="text-nowrap">{{ $list->fam_suffix }}</td>
                <td class="text-nowrap">{{ $list->fam_maidenname }}</td>
                <td class="text-nowrap">{{ $list->fam_birthdate }}</td>
                <td>{{ $list->age }}</td>
                <td>{{ $list->fam_sex }}</td>
                <td class="text-nowrap">{{ $list->fam_contact }}</td>
                <td>{{ $list->fam_add }}</td>
                <td>{{ $list->fam_occupation }}</td>
                <td>{{ $list->fam_workplace }}</td>
                <td>
                    <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1"
                        data-famid="{{ $list->fam_id }}"
                        data-relationship="{{ $list->fam_relationship }}"
                        data-lastname="{{ $list->fam_lastname }}"
                        data-firstname="{{ $list->fam_firstname }}"
                        data-middlename="{{ $list->fam_midname }}"
                        data-suffix="{{ $list->fam_suffix }}"
                        data-maidenname="{{ $list->fam_maidenname }}"
                        data-birthdate="{{ $list->fam_birthdate }}"
                        data-sex="{{ $list->fam_sex }}"
                        data-contact="{{ $list->fam_contact }}"
                        data-address="{{ $list->fam_add }}"
                        data-occupation="{{ $list->fam_occupation }}"
                        data-workplace="{{ $list->fam_workplace }}"
                        onclick="edit_family(this)">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm m-1" data-famid="{{ $list->fam_id }}" onclick="remove_family(this)">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_family(this)">Add</button>
</div>

<form id="form-family" name="form-family" method="post" action="{{ route('family.store') }}" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="family-id" id="family-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="family-relationship" id="family-relationship" aria-label="">
                    <option selected>-Select-</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Mother">Mother</option>
                    <option value="Father">Father</option>
                    <option value="Son">Son</option>
                    <option value="Daughter">Daughter</option>
                    <option value="Sister">Sister</option>
                    <option value="Brother">Brother</option>
                    <option value="Live-in Partner">Live-in Partner</option>
                </select>
                <label for="family-relationship">Relationship</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="family-firstname" id="family-firstname">
                <label for="family-firstname">First Name</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="family-middlename" id="family-middlename">
                <label for="family-middlename">Middle Name</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="family-lastname" id="family-lastname">
                <label for="family-lastname">Last Name</label>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="family-suffix" id="family-suffix">
                <label for="family-suffix">Suffix</label>
            </div>
        </div>
    </div>

    <!-- Basic Info -->
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="family-maidenname" id="family-maidenname">
                <label for="family-maidenname">Maiden Name</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="date" class="form-control-plaintext border-bottom" name="family-birthdate" id="family-birthdate">
                <label for="family-birthdate">Birth Date</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="family-sex" id="family-sex" aria-label="">
                    <option selected>-Select-</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <label for="family-sex">Sex</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="family-contact" id="family-contact">
                <label for="family-contact">Contact #</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="family-address" id="family-address">
                <label for="family-address">Address</label>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="family-occupation" id="family-occupation">
                <label for="family-occupation">Occupation</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="family-workplace" id="family-workplace">
                <label for="family-workplace">Work Address</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Save</button>
    <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-family">Cancel</button>
</form>

@stop