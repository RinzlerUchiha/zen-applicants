@extends('layouts.layout')

@section('content')

<style>
    #form-certificate input,
    #form-certificate select,
    #certificate-list {
        font-size: 12px;
    }

    #certificate-list {
        min-width: 50vw;
        width: fit-content;
    }
</style>

<script type="text/javascript">
    $(function(){
        $('#btn-cancel-edit-certificate').click(function(){
            $('#form-certificate input, #form-certificate select').val('');
            $('#form-certificate').toggleClass('d-none');
            $('#certificate-list').toggleClass('d-none');
        });
    });

    function edit_certificate(e) {
        $('#certificate-id').val($(e).data('certid'));
        $('#certificate-title').val($(e).data('title'));
        $('#certificate-completion-date').val($(e).data('completiondate'));
        $('#certificate-location').val($(e).data('location'));
        $('#certificate-speaker').val($(e).data('speaker'));
        $('#certificate-attachment-current').val($(e).data('attachment'));

        $('#form-certificate').toggleClass('d-none');
        $('#certificate-list').toggleClass('d-none');
    }

    async function remove_certificate(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const url = @json(route('certificate.delete', ['id' => ':id'])).replace(':id', $(e).data('certid'));
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

<div id="certificate-list">
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
    <table class="table table-sm table-striped table-hover" id="certificate-list-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Completion Date</th>
                <th>Location of Event/Course</th>
                <th>Speaker</th>
                <th>Attachment</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($certificate as $list)
            <tr>
                <td class="text-nowrap">{{ $list->cert_title }}</td>
                <td class="text-nowrap">{{ $list->cert_date }}</td>
                <td class="text-nowrap">{{ $list->cert_address }}</td>
                <td class="text-nowrap">{{ $list->cert_speaker }}</td>
                <td>
                    @if($list->cert_file)
                        <embed src="{{ '/file/get/certificate/'.$list->cert_file }}" style="max-width: 100%; height: 150px;">
                    @endif
                </td>
                <td>
                    <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1"
                        data-certid="{{ $list->cert_id }}"
                        data-title="{{ $list->cert_title }}"
                        data-completiondate="{{ $list->cert_date }}"
                        data-location="{{ $list->cert_address }}"
                        data-speaker="{{ $list->cert_speaker }}"
                        data-attachment="{{ $list->cert_file }}"
                        onclick="edit_certificate(this)">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm m-1" data-certid="{{ $list->cert_id }}" onclick="remove_certificate(this)">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_certificate(this)">Add</button>
</div>

<form id="form-certificate" enctype="multipart/form-data" name="form-certificate" method="post" action="{{ route('certificate.store') }}" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="certificate-id" id="certificate-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="certificate-title" id="certificate-title">
                <label for="certificate-title">Title</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="date" class="form-control-plaintext border-bottom" name="certificate-completion-date" id="certificate-completion-date">
                <label for="certificate-completion-date">Completion Date</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="certificate-location" id="certificate-location">
                <label for="certificate-location">Location of Event/Course</label>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="certificate-speaker" id="certificate-speaker">
                <label for="certificate-speaker">Speaker</label>
            </div>
        </div>
        
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="file" class="form-control-plaintext border-bottom" name="certificate-attachment" id="certificate-attachment">
                <input type="hidden" name="certificate-attachment-current" id="certificate-attachment-current">
                <label for="certificate-attachment">Attachment</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Save</button>
    <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-certificate">Cancel</button>
</form>

@stop