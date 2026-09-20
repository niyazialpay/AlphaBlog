<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Panel\PanelResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $notifications = $user->notifications()->paginate(10);

        return PanelResponse::render(
            'Notifications/Index',
            'panel.notifications.index',
            [
                'notifications' => PanelResponse::rows($notifications, fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? null,
                    'message' => $notification->data['message'] ?? null,
                    'url' => $notification->data['url'] ?? null,
                    'readAt' => $notification->read_at?->toIso8601String(),
                    'createdAt' => $notification->created_at?->toIso8601String(),
                    'ago' => $notification->created_at?->diffForHumans(),
                ]),
            ],
            compact('notifications'),
        );
    }

    public function readAndRedirect(Request $request)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($request->id);

        if ($notification) {
            $notification->markAsRead();

            return redirect($notification->data['url']);
        }

        return redirect()->route('notifications.index');
    }

    public function markAsRead($id, Request $request)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return redirect()->route('notifications.index');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($request->id);

        if ($notification) {
            $notification->delete();

            return $request->inertia()
                ? back()->with('success', __('notifications.notification_deleted'))
                : response()->json([
                    'result' => 'success',
                    'message' => __('notifications.notification_deleted'),
                ]);
        }

        return $request->inertia()
            ? back()->with('error', __('notifications.notification_not_found'))
            : response()->json([
                'result' => 'error',
                'message' => __('notifications.notification_not_found'),
            ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index');
    }

    public function deleteAll(Request $request)
    {
        $user = $request->user();
        $user->notifications()->delete();

        return $request->inertia()
            ? back()->with('success', __('notifications.notifications_deleted'))
            : response()->json([
                'result' => 'success',
                'message' => __('notifications.notifications_deleted'),
            ]);
    }
}
