<?php

/*
|--------------------------------------------------------------------------
| Vue'ya taşınmış panel route'ları — TEK MİGRASYON DEFTERİ
|--------------------------------------------------------------------------
|
| Burada listelenen route adları (Str::is desenleri) Inertia ile sunulur.
| Listede OLMAYAN her panel route'u hâlâ eski AdminLTE Blade kabuğundadır ve
| ona gidiş tam sayfa yüklemesi olmalıdır: yanıtta X-Inertia başlığı yoktur,
| `router.visit()` client tarafında hata verir.
|
| Bu liste hem sunucuda (sidebar öğelerinin `inertia` bayrağı) hem istemcide
| (`inertiaRoutes` paylaşılan prop'u; komut paleti, bildirim zili, çapraz
| bağlantılar) kullanılır — böylece defter tek yerde tutulur.
|
| Bir ekran taşındığında yapılacak tek şey: route adını buraya eklemek.
|
| Modül route'ları da aynı deftere girer, ör. 'panel.valefix.customers.*'.
*/

return [

    'admin.about',
    'admin.monitoring.*',
    'notifications.index',
    'admin.contact_messages',
    'adminRoutes',
    'admin.system-logs',
    'admin.search.index',

    // Faz 3 - icerik cekirdegi
    'admin.posts',
    'admin.post.category',
    'admin.post.create',
    'admin.post.edit',
    'admin.categories',
    'admin.post.comments',
    'admin.post.media',
    'admin.post.history',
    'admin.post.history.show',

    // Faz 4
    'admin.users',
    'admin.user.create',
    'admin.notes',
    'admin.notes.create',
    'admin.notes.edit',
    'admin.notes.show',
    'admin.notes.media',
    'admin.notes.categories',
    'admin.notes.category',
    'admin.menu.index',
    'admin.menu.show',
    'admin.profile.index',
    'admin.user.edit',

    // Faz 5
    'admin.settings',
    'admin.ip-filter',
    'admin.ip-filter.create',
    'admin.ip-filter.show',
    'admin.firewall',
    'admin.firewall.logs',
    // Cloudflare ekranlari canli CF API kimlik bilgisi ister; duman testine
    // alinmaz (gecersiz kimlikte zaten admin.settings?tab=cloudflare'e doner).
    'cf.dashboard',
    'cf.dns',

    // Faz 6 — raporlar + sohbet
    'admin.contact_page',
    'admin.analytics',
    'admin.search-console',
    'chatbot',
    'admin.index',

    // Faz 1 — Auth ekranları
    'login',
    'forgot-password',
    'password.reset',
    'verification.notice',

    // Faz 7 — ValeFix
    'panel.valefix.appointments',
    'panel.valefix.appointments.show',
    'panel.valefix.customers',
    'panel.valefix.customers.show',
    'panel.valefix.fault-categories.create',
    'panel.valefix.fault-categories.edit',
    'panel.valefix.fault-categories.index',
    'panel.valefix.fault-knowledge.create',
    'panel.valefix.fault-knowledge.edit',
    'panel.valefix.fault-knowledge.index',
    'panel.valefix.fault-knowledge.show',
    'panel.valefix.fault-types.create',
    'panel.valefix.fault-types.edit',
    'panel.valefix.fault-types.index',
    'panel.valefix.home',
    'panel.valefix.reports',
    'panel.valefix.reports.show',
    'panel.valefix.services',
    'panel.valefix.services.create',
    'panel.valefix.services.edit',
    'panel.valefix.settings',
    'panel.valefix.staff',
    'panel.valefix.staff.create',
    'panel.valefix.staff.edit',
    'panel.valefix.theme.index',
    'panel.valefix.vehicles',

    // Faz 7 — CihanSK
    'panel.cihansk.belts',
    'panel.cihansk.belts.create',
    'panel.cihansk.belts.edit',
    'panel.cihansk.dues',
    'panel.cihansk.dues.settings',
    'panel.cihansk.expense-categories',
    'panel.cihansk.expense-categories.create',
    'panel.cihansk.expense-categories.edit',
    'panel.cihansk.expenses',
    'panel.cihansk.expenses.create',
    'panel.cihansk.expenses.edit',
    'panel.cihansk.galleries',
    'panel.cihansk.galleries.create',
    'panel.cihansk.galleries.edit',
    'panel.cihansk.galleries.items',
    'panel.cihansk.galleries.items.upload',
    'panel.cihansk.gym',
    'panel.cihansk.gym.create',
    'panel.cihansk.gym.edit',
    'panel.cihansk.gym.settings',
    'panel.cihansk.home',
    'panel.cihansk.report',
    'panel.cihansk.students',
    'panel.cihansk.students.create',
    'panel.cihansk.students.edit',
    'panel.cihansk.students.show',
    'panel.cihansk.testimonials',
    'panel.cihansk.testimonials.create',
    'panel.cihansk.testimonials.edit',
    'panel.cihansk.trainers',
    'panel.cihansk.trainers.create',
    'panel.cihansk.trainers.edit',
    'panel.cihansk.trainers.settings',

    // Faz 7 — BirderAkademi
    'panel.birderakademi.approvals.index',
    'panel.birderakademi.categories.edit',
    'panel.birderakademi.categories.index',
    'panel.birderakademi.concepts.edit',
    'panel.birderakademi.concepts.index',
    'panel.birderakademi.digests.edit',
    'panel.birderakademi.digests.index',
    'panel.birderakademi.home.edit',
    'panel.birderakademi.home.index',
    'panel.birderakademi.indicators.create',
    'panel.birderakademi.indicators.edit',
    'panel.birderakademi.indicators.index',
    'panel.birderakademi.indices.edit',
    'panel.birderakademi.indices.index',
    'panel.birderakademi.infographics.edit',
    'panel.birderakademi.infographics.index',
    'panel.birderakademi.logs.index',
    'panel.birderakademi.reports.edit',
    'panel.birderakademi.reports.index',
    'panel.birderakademi.settings',
    'panel.birderakademi.sources.index',

    // Faz 7 — EDergi
    'panel.edergi.create',
    'panel.edergi.edit',
    'panel.edergi.index',
    'panel.edergi.preview',
    'panel.edergi.settings',
    'panel.edergi.stats',

    // Faz 7 — Podcast (modules_statuses.json'da devre disi; route'lari
    // kayitli degil, bu yuzden defter testi devre disi modulleri atlar)
    'panel.podcast.index',
    'panel.podcast.deleted',
    'panel.podcast.episodes',
    'panel.podcast.episodes.deleted',
    'panel.podcast.channels',
    'panel.podcast.channels.add-edit',
    'panel.podcast.create',
    'panel.podcast.edit',
    'panel.podcast.episodes.create',
    'panel.podcast.episodes.edit',

    // Faz 7 — Birdergi
    'panel.birdergi.bulk-mail',
    'panel.birdergi.settings',
    'panel.birdergi.subscribers.index',

    // Faz 7 — XSayfaMuhasebe
    'panel.xsayfa.muhasebe.index',
];
