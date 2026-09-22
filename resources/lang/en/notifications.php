<?php

return [
    'notifications' => 'Notifications',
    'read' => 'Read',
    'unread' => 'Unread',
    'mark_as_read' => 'Mark as Read',
    'mark_as_unread' => 'Mark as Unread',
    'mark_as_read_success' => 'Notification marked as read successfully.',
    'mark_as_unread_success' => 'Notification marked as unread successfully.',
    'mark_as_read_error' => 'An error occurred while marking the notification as read.',
    'mark_as_unread_error' => 'An error occurred while marking the notification as unread.',
    'you_have_no_notifications' => 'You have no notifications',
    'all_notifications' => 'All Notifications',
    'notification_deleted' => 'Notification deleted successfully.',
    'notification_not_found' => 'Notification not found.',
    'delete_error' => 'An error occurred while deleting the notification.',
    'delete_warning' => "You won't be able to revert this!",
    'unread_notifications' => '{0} There are no notifications|{1} :count Unread Notification|[2,*] :count Unread Notifications',
    'mark_all_as_read' => 'Mark All as Read',
    'delete_all' => 'Delete All',
    'notifications_deleted' => 'Notifications deleted successfully.',
    'no_notifications' => 'You have no notifications',

    /*
     * Notification event labels.
     *
     * Keys mirror the registry in App\Support\Notifications\NotificationEvents;
     * a new event needs a line here too, otherwise the preference screen shows
     * the raw key.
     */
    'events' => [
        'comment_created' => 'New comment',
        'contact_message' => 'New contact message',
    ],

    /* Preference screen */
    'preferences_tab' => 'Notification Settings',
    'preferences_intro' => 'Choose which events you want to be notified about. The panel bell applies to every device; browser notifications are only sent to the devices where you granted permission.',
    'preferences_saved' => 'Your notification preferences have been saved.',
    'preferences_error' => 'An error occurred while saving your notification preferences.',
    'event' => 'Event',
    'channel_database' => 'Panel bell',
    'channel_push' => 'Browser',

    /* Browser (web push) subscription */
    'push_title' => 'Browser notifications',
    'push_on' => 'On',
    'push_enable' => 'Enable notifications',
    'push_disable' => 'Disable notifications',
    'push_settings' => 'Notification settings',
    'push_subscribed' => 'Browser notifications are enabled for this device.',
    'push_unsubscribed' => 'Browser notifications are disabled for this device.',
    'push_error' => 'Browser notifications could not be enabled. Please try again.',
    'push_unsupported' => 'This browser does not support notifications.',
    'push_blocked' => 'Notifications are blocked in your browser settings.',
    'push_blocked_hint' => 'Allow notifications from the site settings in your address bar, then reload the page.',
    'push_device_title' => 'This device',
    'push_device_on' => 'This device is subscribed to browser notifications.',
    'push_device_off' => 'This device is not subscribed to browser notifications.',

    /* First-visit card */
    'push_prompt_title' => 'Turn on notifications?',
    'push_prompt_body' => 'Stay on top of new comments and messages even when the panel is closed. You can revoke the permission at any time.',
    'push_prompt_dismiss' => 'Not now',
];
