<a class="nav-link tooltip-button" data-bs-toggle="dropdown" href="javascript:void(0);"
   data-bs-placement="bottom"
   title="@lang('notifications.notifications')"
   aria-expanded="false">
    <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-bell"></i>
    @if($total_unread_notifications>0)
    <span class="badge badge-danger navbar-badge">{{$total_unread_notifications}}</span>
    @endif
</a>
<div class="dropdown-menu dropdown-menu-xl dropdown-menu-right" style="left: inherit; right: 0px;">
    <span class="dropdown-item dropdown-header">{{trans_choice('notifications.unread_notifications', $total_unread_notifications)}}</span>
    <div class="dropdown-divider"></div>
    @foreach($notifications as $notification)
        <a href="{{route('notifications.readAndRedirect', $notification->id)}}"
           data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $notification->data['title'] }}"
           class="dropdown-item">
            <span class="notification-message"><i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-bell"></i> {{ $notification->data['message'] }}</span>
            <span class="float-right text-muted text-sm">{{ $notification->created_at->diffForHumans() }}</span>
        </a>
    @endforeach
    <div class="dropdown-divider"></div>
    <a href="{{route('notifications.index')}}" class="dropdown-item dropdown-footer">@lang('notifications.all_notifications')</a>
</div>
