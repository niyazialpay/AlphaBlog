@extends('panel.base')
@section('title', __('notes.notes'))

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            @lang('notes.notes')
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex">
                <h3 class="card-title">
                    Notes
                </h3>
                <div class="ml-auto">
                    <a href="{{route('admin.notes.create')}}"
                       class="btn btn-outline-primary"
                       data-bs-toggle="tooltip" data-bs-placement="top"
                       title="@lang('general.new')">
                        <i class="fa-duotone fa-comment-plus"></i>
                        @lang('general.new')
                    </a>
                </div>
            </div>
            <p class="d-block bg-warning text-center my-2 p-2 rounded text-danger">
                @lang('notes.encrypted_note')
            </p>
        </div>
        <div class="card-body table-responsive">
            <p class="text-center mt-2">
                <a href="javascript:$('#encryption_key_modal').modal('show')" class="btn btn-default">
                    @lang('notes.define_encryption_key')
                </a>
            </p>
            <table class="table table-striped" aria-describedby="notes">
                <thead>
                <tr>
                    <th scope="col">@lang('post.title')</th>
                    <th scope="col" class="text-center" style="width: 200px">@lang('general.created_at')</th>
                    <th scope="col" class="text-center" style="width: 200px">@lang('general.updated_at')</th>
                    <th scope="col" style="width: 150px">@lang('general.actions')</th>
                </thead>
                <tbody>
                @forelse($notes as $note)
                    <tr>
                        <td>
                            <a href="{{route('admin.notes.show', $note)}}">
                                {{$note->title}}
                            </a>
                        </td>
                        <td class="text-center">
                            {{$note->created_at}}
                        </td>
                        <td class="text-center">
                            {{$note->updated_at}}
                        </td>
                        <td>
                            <a href="{{route('admin.notes.media', $note)}}" class="btn btn-sm btn-primary"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               data-bs-title="@lang('post.media')">
                                <i class="fa-solid fa-images"></i>
                            </a>
                            <a href="{{route('admin.notes.edit', $note)}}" class="btn btn-sm btn-primary"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               data-bs-title="@lang('general.edit')">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="javascript:DeleteNote('{{$note->id}}')" class="btn btn-sm btn-danger"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               data-bs-title="@lang('general.delete')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            @lang('notes.not_found')
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('script')
    @include('panel.personal_notes.scripts')
    <script>
        function DeleteNote(note_id) {
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: '@lang('general.you_wont_be_able_to_revert_this')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '@lang('general.delete_confirm_yes')',
                cancelButtonText: '@lang('general.cancel')',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.notes')}}/delete/' + note_id,
                        type: 'POST',
                        success: function (response) {
                            Swal.fire({
                                title: '@lang('general.deleted')',
                                text: '@lang('notes.deleted')',
                                icon: 'success',
                                confirmButtonText: '@lang('general.ok')',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            })
                        }
                    })
                }
            })
        }
    </script>
@endsection
