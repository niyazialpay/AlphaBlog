<?php

return [
    'dashboard' => 'Kontrol Paneli',
    'top_pages' => 'En Çok Ziyaret Edilen Sayfalar',
    'top_countries' => 'En Çok Ziyaretçi Gelen Ülkeler',
    'top_browsers' => 'En Çok Kullanılan Tarayıcılar',
    'top_operating_systems' => 'En Çok Kullanılan İşletim Sistemleri',
    'ad_impression' => 'Reklam Gösterimleri',
    'sessions' => 'Oturumlar',
    'ad_clicks' => 'Reklam Tıklamaları',
    'total_visitors_and_page_views' => 'Toplam Ziyaretçi ve Sayfa Görüntüleme',
    'total_visitors' => 'Toplam Ziyaretçi',
    'total_page_views' => 'Toplam Sayfa Görüntüleme',
    'user_types' => 'Kullanıcı Türleri',
    'page_views' => 'Sayfa Görüntüleme',
    'views' => 'Görüntüleme',

    'user_type' => [
        'new' => 'Yeni Ziyaretçiler',
        'returning' => 'Geri Gelen Ziyaretçiler',
        'others' => 'Diğerleri',
    ],
    'back_to_dashboard' => 'Kontrol Paneline Geri Dön',
    'analytics' => 'Google Analytics Verileri',
    'filter' => 'Filtrele',

    /*
     * Widget'lar ve rapor ekranları için AYIRT EDİLEBİLİR boş durumlar.
     * Daha önce hepsi general.not_available ("Bu servis şu an kullanılamıyor.")
     * ile gösteriliyordu; kurulmamış entegrasyon, patlamış istek ve gerçekten
     * boş bir tarih aralığı aynı mesajı veriyordu.
     */
    'ga4_not_configured' => 'Google Analytics bağlantısı yapılandırılmamış.',
    'ga4_not_configured_hint' => 'storage/app/analytics/service-account-credentials.json dosyasının yüklü olduğundan ve .env içindeki ANALYTICS_PROPERTY_ID değerinin dolu olduğundan emin olun.',
    'gsc_not_configured' => 'Search Console bağlantısı yapılandırılmamış.',
    'gsc_not_configured_hint' => 'Kimlik dosyasını yükleyin ve Google Indexing ayarlarından site adresini girin.',
    'data_fetch_failed' => 'Veri alınamadı.',
    'data_fetch_failed_hint' => 'Google isteği başarısız oldu. Ayrıntı için sistem günlüklerine bakın.',
    'resize_hint' => 'Boyutlandırmak için köşeden sürükleyin',
];
