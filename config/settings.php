<?php

return [
    'admin_panel_path' => env('ADMIN_PANEL_PATH', 'admin'),
    'notification_send_method' => env('NOTIFICATION_SEND_METHOD', 'directly'),
    'mail_send_method' => env('MAIL_SEND_METHOD', 'directly'),
    'fontawesome_pro' => env('FONTAWESOME_PRO', false),

    'panel_ui' => env('PANEL_UI', 'vue'),

    'panel_ui_screens' => env('PANEL_UI_SCREENS', ''),
];
