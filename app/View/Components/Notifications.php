<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Notifications extends Component
{
    public $notifications;

    public $total_unread_notifications;

    public function __construct()
    {
        $this->notifications = request()->user()->unreadNotifications;
        $this->total_unread_notifications = request()->user()->unreadNotifications()->count();

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('panel.components.notifications', [
            'notifications' => $this->notifications,
            'total_unread_notifications' => $this->total_unread_notifications,
        ]);
    }
}
