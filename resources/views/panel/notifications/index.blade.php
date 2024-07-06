@extends('panel.base')
@section('title',__('menu.menu'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">@lang('notifications.notifications')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h3 class="card-title">@lang('notifications.notifications')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <ul>
                        @foreach ($notifications as $notification)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    @if($notification->read_at == null)
                                        <strong>
                                    @endif
                                    <i class="fas fa-bell"></i> {{ $notification->data['message'] }}
                                    <br>
                                    <a href="{{ $notification->data['url'] }}">
                                        {{ $notification->data['title'] }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    @if($notification->read_at == null)
                                         </strong>
                                    @endif
                                </div>
                                <div class="d-flex">
                                    @if($notification->read_at == null)
                                        <a href="{{ route('notifications.markAsRead', $notification->id) }}"
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('notifications.mark_as_read')"
                                           class="btn btn-sm btn-primary me-2">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @endif
                                    <button class="btn btn-sm btn-danger delete-btn"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('general.delete')"
                                            data-id="{{ $notification->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteButtons = document.querySelectorAll('.delete-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const notificationId = this.getAttribute('data-id');

                    Swal.fire({
                        title: '@lang('general.are_you_sure')',
                        text: "@lang('notifications.delete_warning')",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '@lang('general.delete_confirm_yes')!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route('notifications.destroy') }}',
                                type: 'DELETE',
                                data: {
                                    id: notificationId,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (response) {
                                    if (response.result === 'success') {
                                        location.reload();
                                    } else {
                                        Swal.fire(
                                            '@lang('general.error')!',
                                            '@lang('notifications.delete_error')',
                                            'error'
                                        );
                                    }
                                }
                            });
                        } else {
                            Swal.fire({
                                title: '@lang('general.error')!',
                                text: '@lang('notifications.delete_error')',
                                icon: 'error',
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1500
                            })
                        }
                    });
                });
            });
        });
    </script>
@endsection
