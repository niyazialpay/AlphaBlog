<a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false">
    <i class="fa-duotone fa-bell"></i>
    <span class="badge badge-danger navbar-badge">{{$total_unread_notifications}}</span>
</a>
<div class="dropdown-menu dropdown-menu-xl dropdown-menu-right" style="left: inherit; right: 0px;">
    <span class="dropdown-item dropdown-header">{{trans_choice('notifications.unread_notifications', $total_unread_notifications)}}</span>
    <div class="dropdown-divider"></div>
    @foreach($notifications as $notification)
        <a href="{{route('notifications.readAndRedirect', $notification->id)}}"
           data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $notification->data['title'] }}"
           class="dropdown-item">
            <span class="notification-message"><i class="fa-duotone fa-bell"></i> {{ $notification->data['message'] }}</span>
            <span class="float-right text-muted text-sm">{{ $notification->created_at->diffForHumans() }}</span>
        </a>
    @endforeach
    <div class="dropdown-divider"></div>
    <a href="{{route('notifications.index')}}" class="dropdown-item dropdown-footer">@lang('notifications.all_notifications')</a>
</div>
