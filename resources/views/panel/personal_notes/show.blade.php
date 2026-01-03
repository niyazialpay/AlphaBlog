@extends('panel.base')
@section('title',$note->title)
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.notes')}}">@lang('notes.notes')</a>
        </li>
        <li class="breadcrumb-item active">{{$note->title}}</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h3 class="card-title">{{$note->category->name}}</h3>
            <div class="ml-auto">
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
                <a href="javascript:DeleteNote()" class="btn btn-sm btn-danger"
                   data-bs-toggle="tooltip" data-bs-placement="top"
                   data-bs-title="@lang('general.delete')">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    {!! $note->content !!}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function DeleteNote(){
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: '@lang('general.you_wont_be_able_to_revert_this')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '@lang('general.delete_confirm_yes')',
                cancelButtonText: '@lang('general.cancel')',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.notes.delete', $note)}}',
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire(
                                    '@lang('general.deleted')!',
                                    '@lang('notes.deleted')',
                                    'success'
                                ).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '{{route('admin.notes')}}';
                                    }
                                });
                            } else {
                                Swal.fire(
                                    '@lang('general.error')!',
                                    '@lang('general.something_went_wrong')',
                                    'error'
                                );
                            }
                        }
                    });
                }
            });
        }
    </script>
@endsection
