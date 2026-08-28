@extends('layouts.layout')

@section('content')

<style>
    #form-education input,
    #form-education select,
    #education-list {
        font-size: 12px;
    }

    #education-list {
        min-width: 50vw;
        width: fit-content;
    }
</style>

<script type="text/javascript">
    $(function(){
        $('#btn-cancel-edit-education').click(function(){
            $('#form-education input, #form-education select').val('');
            $('#form-education').toggleClass('d-none');
            $('#education-list').toggleClass('d-none');
        });
    });

    function edit_education(e) {
        $('#education-id').val($(e).data('eduid'));
        $('#education-level').val($(e).data('level'));
        $('#education-degree').val($(e).data('degree'));
        $('#education-major').val($(e).data('major'));
        $('#education-school').val($(e).data('school'));
        $('#education-address').val($(e).data('address'));
        $('#education-year-graduated').val($(e).data('yeargrad'));
        $('#education-curstat').val($(e).data('curstat'));

        $('#form-education').toggleClass('d-none');
        $('#education-list').toggleClass('d-none');
    }

    async function remove_education(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const url = @json(route('education.delete', ['id' => ':id'])).replace(':id', $(e).data('eduid'));
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

<div id="education-list">
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
    <table class="table table-sm table-striped table-hover mb-5" id="education-list-table">
        <thead>
            <tr>
                <th>Level</th>
                <th>School</th>
                <th>Address</th>
                <th>Degree Title</th>
                <th>Major</th>
                <th>Year Graduated</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($education as $list)
            <tr>
                <td class="text-nowrap">{{ $list->educ_level }}</td>
                <td class="text-nowrap">{{ $list->educ_school }}</td>
                <td class="text-nowrap">{{ $list->educ_schooladd }}</td>
                <td>{{ $list->educ_degreetitle }}</td>
                <td>{{ $list->educ_major }}</td>
                <td class="text-nowrap">{{ $list->educ_yeargrad }}</td>
                <td class="text-nowrap">{{ $list->educ_currStatus }}</td>
                <td>
                    <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1"
                        data-eduid="{{ $list->educ_id }}"
                        data-level="{{ $list->educ_level }}"
                        data-degree="{{ $list->educ_degreetitle }}"
                        data-major="{{ $list->educ_major }}"
                        data-school="{{ $list->educ_school }}"
                        data-address="{{ $list->educ_schooladd }}"
                        data-yeargrad="{{ $list->educ_yeargrad }}"
                        data-curstat="{{ $list->educ_currStatus }}"
                        onclick="edit_education(this)">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm m-1" data-eduid="{{ $list->educ_id }}" onclick="remove_education(this)">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_education(this)">Add</button>
</div>

<form id="form-education" name="form-education" method="post" action="{{ route('education.store') }}" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="education-id" id="education-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="education-level" id="education-level" aria-label="">
                    <option selected>-Select-</option>
                    <option value="Primary">Primary</option>
                    <option value="Secondary">Secondary</option>
                    <option value="Tertiary">Tertiary</option>
                </select>
                <label for="education-level">Level</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="education-degree" id="education-degree">
                <label for="education-degree">Degree Title</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="education-major" id="education-major">
                <label for="education-major">Major</label>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="education-school" id="education-school">
                <label for="education-school">School</label>
            </div>
        </div>
        
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="education-address" id="education-address">
                <label for="education-address">Address</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="number" min="1970" class="form-control-plaintext border-bottom" name="education-year-graduated" id="education-year-graduated">
                <label for="education-year-graduated">Year Graduated</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <select class="form-control-plaintext border-bottom" name="education-curstat" id="education-curstat" aria-label="">
                    <option selected>-Select-</option>
                    <option value="Completed">Completed</option>
                    <option value="Graduated">Graduated</option>
                </select>
                <label for="education-curstat">Status</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Save</button>
    <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-education">Cancel</button>
</form>

@stop