<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->paginate(10);

        return view('panel.notifications.index', compact('notifications'));
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

            return response()->json([
                'result' => 'success',
                'message' => __('notifications.notification_deleted'),
            ]);
        }

        return response()->json([
            'result' => 'error',
            'message' => __('notifications.notification_not_found'),
        ]);
    }
}
