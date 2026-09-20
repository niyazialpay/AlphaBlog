<?php

return [
    'admin_panel_path' => env('ADMIN_PANEL_PATH', 'admin'),
    'notification_send_method' => env('NOTIFICATION_SEND_METHOD', 'directly'),
    'mail_send_method' => env('MAIL_SEND_METHOD', 'directly'),
    'fontawesome_pro' => env('FONTAWESOME_PRO', false),

    /*
     * Panel arayüzü: 'vue' (Inertia) veya 'blade' (eski AdminLTE kabuğu).
     * Geri dönüş için derleme gerekmez: .env + config:cache yeterli.
     */
    'panel_ui' => env('PANEL_UI', 'vue'),

    /*
     * Boş => taşınmış tüm ekranlar Vue. Doluysa yalnızca listelenen bileşenler
     * (ör. "Dashboard,Posts") Vue olarak sunulur, kalanlar Blade'e düşer.
     */
    'panel_ui_screens' => env('PANEL_UI_SCREENS', ''),
];
