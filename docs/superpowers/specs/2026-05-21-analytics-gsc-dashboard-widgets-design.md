# Analytics Enhancement, GSC Page & Dashboard Widget System

**Date:** 2026-05-21  
**Scope:** Analytics page overhaul, new GSC performance page, customizable dashboard widget system, GSC credential unification

---

## 1. GSC Credential Unification

### Problem
`GoogleIndexingAction.php` reads credentials from `general_settings.google_indexing_credentials` (encrypted DB column). Spatie Analytics already uses `storage/app/analytics/service-account-credentials.json`. Two credential stores for the same service account is redundant.

### Change
- `GoogleIndexingAction.php`: replace `$settings->google_indexing_credentials` read with `file_get_contents(storage_path('app/analytics/service-account-credentials.json'))`
- Remove credentials upload UI from `resources/views/panel/settings/index.blade.php`
- Remove credentials save logic from `GeneralSettingsController`
- `google_indexing_credentials` column stays in DB (no destructive migration), just stops being written/read
- `google_indexing_site_url` setting stays — still needed for GSC API calls

---

## 2. Analytics Page Enhancement

**File:** `resources/views/panel/analytics.blade.php`  
**Controller:** `app/Http/Controllers/Admin/AnalyticsController.php`

### New Data: Overview Metrics with % Changes

`AnalyticsController::extracted()` fetches two periods:
- **Current period:** selected date range (default: last 7 days)
- **Previous period:** same-length window immediately before current

GA4 metrics fetched via `Analytics::get()` with a single `runReport` call per period:
- `activeUsers`, `newUsers`, `screenPageViews`, `eventCount`, `userEngagementDuration`, `sessions`
- No dimensions (aggregated totals)

Controller computes % change: `($current - $previous) / $previous * 100`. Returns `overview` array with current values and deltas.

Average engagement time derived from `userEngagementDuration / sessions` (seconds → formatted "Xm Ys").

### New Data: Trend Chart (Current vs Previous Period)

Existing `fetchTotalVisitorsAndPageViews()` returns `activeUsers` + `screenPageViews` by date. Add a second call for the previous period. Controller returns both series. Blade renders ApexCharts line chart with two series (current: solid, previous: dashed).

### Blade Changes

Top of page (before date range picker row):
```
[ Aktif Kullanıcı ▲12% ] [ Yeni Kullanıcı ▼3% ] [ Sayfa Görüntüleme ▲8% ] [ Etkinlik ▲21% ] [ Ort. Etkileşim ▲5% ]
```

After cards: active users trend line chart (ApexCharts, height 300, current + previous period).

Existing charts (pie × 4, stacked bar, top pages bar) remain unchanged below.

### Caching

Overview metrics cached per `{prefix}analytics_overview_{startDate}_{endDate}` for 15 minutes. Previous period cached separately with same TTL.

---

## 3. Google Search Console Page

### Service: `app/Services/GoogleSearchConsoleService.php`

Single responsibility: call GSC Search Analytics API.

```php
class GoogleSearchConsoleService
{
    public function getPerformance(string $siteUrl, Carbon $startDate, Carbon $endDate): array
    public function getKeywords(string $siteUrl, Carbon $startDate, Carbon $endDate, int $limit = 50): array
    public function getClicksTrend(string $siteUrl, Carbon $startDate, Carbon $endDate): array
}
```

- Reads `storage_path('app/analytics/service-account-credentials.json')`
- Creates `Google_Client` with scope `https://www.googleapis.com/auth/webmasters.readonly`
- Calls `POST https://searchconsole.googleapis.com/webmasters/v3/sites/{siteUrl}/searchAnalytics/query`
- `getPerformance()`: no dimensions, returns totals (clicks, impressions, CTR, position) for current + previous period
- `getKeywords()`: dimension `query`, ordered by clicks desc, limit 50
- `getClicksTrend()`: dimension `date`, ordered by date asc
- Returns empty arrays if service account file missing or siteUrl not set

### Controller: `app/Http/Controllers/Admin/SearchConsoleController.php`

```php
class SearchConsoleController extends Controller
{
    public function index(Request $request): View
    public function fetch(Request $request): JsonResponse
    
    private function getData(Request $request): array
}
```

- `index()` → `panel.search-console` view with initial data
- `fetch()` → JSON for AJAX date range refresh (same as `AnalyticsController::fetchAnalytics`)
- `getData()`: calls `GoogleSearchConsoleService`, computes % changes, caches 30 min

### Routes: `routes/panel/analytics.php` (new file or append existing)

```php
Route::get('/search-console', [SearchConsoleController::class, 'index'])->name('admin.search-console');
Route::post('/search-console/fetch', [SearchConsoleController::class, 'fetch'])->name('admin.search-console.fetch');
```

### View: `resources/views/panel/search-console.blade.php`

Layout mirrors `analytics.blade.php` structure:

1. **Date range picker** (daterangepicker, same as analytics page)
2. **4 summary cards** with % change badges: Toplam Tıklama, Toplam Gösterim, Ort. CTR, Ort. Konum
3. **Trend chart** (ApexCharts line, dual Y-axis): clicks (left axis, green) + impressions (right axis, blue)
4. **Keywords table**: columns = Anahtar Kelime | Tıklama | Gösterim | TO | Konum. Client-side text filter. Sortable columns (JS). Position badge colors: 1–3 green, 4–10 yellow, 11+ red.

### Sidebar Navigation

Add "Search Console" link to admin sidebar in `resources/views/panel/base.blade.php` under the Analytics link.

### Error State

If `service-account-credentials.json` missing or `google_indexing_site_url` empty: show info alert "Search Console entegrasyonu yapılandırılmamış." instead of data.

---

## 4. Dashboard Widget System

### Migration: `create_dashboard_widgets_table`

```
id (bigint PK)
user_id (bigint FK → users.id, cascade delete)
widget_type (varchar 60)
gs_x (tinyint)   -- gridstack x
gs_y (tinyint)   -- gridstack y
gs_w (tinyint)   -- gridstack width (1–12)
gs_h (tinyint)   -- gridstack height (1–N)
settings (json nullable)  -- future: widget-specific config
timestamps
```

Index: `(user_id)`.

### Model: `app/Models/DashboardWidget.php`

- `belongsTo(User::class)`
- Casts: `settings` → `array`

### Widget Type Registry: `app/Services/DashboardWidgetService.php`

Central registry of available widget types, their default gridstack sizes, data requirements, and blade partial paths.

```php
const WIDGETS = [
    'ga4_active_users'    => ['label' => 'Aktif Kullanıcı', 'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
    'ga4_new_users'       => ['label' => 'Yeni Kullanıcı',  'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
    'ga4_pageviews'       => ['label' => 'Sayfa Görüntüleme','w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
    'ga4_events'          => ['label' => 'Etkinlik Sayısı',  'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
    'ga4_engagement_time' => ['label' => 'Ort. Etkileşim',  'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
    'ga4_visitors_trend'  => ['label' => 'Ziyaretçi Trendi','w' => 6, 'h' => 4, 'group' => 'GA4 Grafikler'],
    'ga4_browsers'        => ['label' => 'Tarayıcılar',     'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
    'ga4_countries'       => ['label' => 'Ülkeler',         'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
    'ga4_os'              => ['label' => 'İşletim Sistemleri','w' => 3,'h' => 4, 'group' => 'GA4 Grafikler'],
    'ga4_user_types'      => ['label' => 'Kullanıcı Tipi',    'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
    'ga4_top_pages'       => ['label' => 'En Çok Görüntülenen','w' => 6,'h' => 4,'group' => 'GA4 Grafikler'],
    'gsc_clicks'          => ['label' => 'GSC Tıklama',     'w' => 3, 'h' => 2, 'group' => 'Search Console'],
    'gsc_impressions'     => ['label' => 'GSC Gösterim',    'w' => 3, 'h' => 2, 'group' => 'Search Console'],
    'gsc_ctr'             => ['label' => 'GSC CTR',         'w' => 3, 'h' => 2, 'group' => 'Search Console'],
    'gsc_position'        => ['label' => 'GSC Ort. Konum',  'w' => 3, 'h' => 2, 'group' => 'Search Console'],
    'gsc_keywords'        => ['label' => 'Top Keywords',    'w' => 12,'h' => 5, 'group' => 'Search Console'],
    'site_comments'       => ['label' => 'Son Yorumlar',    'w' => 6, 'h' => 4, 'group' => 'Site'],
    'site_firewall'       => ['label' => 'Firewall Logları','w' => 6, 'h' => 4, 'group' => 'Site'],
];
```

`DashboardWidgetService::getDataForWidgets(Collection $widgets): array` — fetches only required data for active widget types (GA4, GSC, comments, firewall), cached 15 min per user.

### Controller Updates: `DashboardController`

- `index()`: loads `auth()->user()->dashboardWidgets`, passes to view with widget data
- New `saveWidgets(Request $request)`: replaces all widgets for current user with posted layout JSON

```php
Route::post('/dashboard/widgets', [DashboardController::class, 'saveWidgets'])
    ->name('admin.dashboard.widgets.save');
```

### View: `dashboard.blade.php`

Replace current hard-coded charts with:

1. **Empty state** (no widgets): centered box with "Dashboard boş" message + "Widget Ekle" button
2. **gridstack container** rendering widget partials:
   ```html
   <div class="grid-stack">
     @foreach($widgets as $widget)
       <div class="grid-stack-item" gs-x="{{ $widget->gs_x }}" gs-y="{{ $widget->gs_y }}" gs-w="{{ $widget->gs_w }}" gs-h="{{ $widget->gs_h }}">
         @include("panel.widgets.{$widget->widget_type}", $widgetData)
       </div>
     @endforeach
   </div>
   ```
3. **Toolbar** (top-right): "Düzenle" toggle button + "Widget Ekle" button (hidden until at least one widget exists, or always shown)
4. **Edit mode**: toggled by "Düzenle" — enables gridstack drag+resize, shows ✕ remove button on each widget
5. **Widget library modal**: Bootstrap modal listing all widget types grouped (GA4/GSC/Site), click to add with default size
6. **Auto-save**: on gridstack `change` event, debounce 500ms, POST to `admin.dashboard.widgets.save`

### Widget Partials: `resources/views/panel/widgets/`

Each file is a self-contained card:
- `ga4_active_users.blade.php` — metric card with value + % badge
- `ga4_visitors_trend.blade.php` — ApexCharts line chart
- `ga4_browsers.blade.php` — ApexCharts pie
- `ga4_user_types.blade.php` — ApexCharts pie
- `gsc_clicks.blade.php` — metric card
- `gsc_keywords.blade.php` — table (top 10, link to full GSC page)
- `site_comments.blade.php` — existing comments table
- `site_firewall.blade.php` — existing firewall table
- etc.

### gridstack.js Integration

Load from CDN in `dashboard.blade.php` script section:
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack/dist/gridstack.min.css">
<script src="https://cdn.jsdelivr.net/npm/gridstack/dist/gridstack-all.js"></script>
```

Initialize with `disableDrag: true, disableResize: true` by default. "Düzenle" toggle calls `grid.enable()` / `grid.disable()`.

---

## 5. Error Handling

- GA4 service account missing → all GA4 data returns `[]`, no exception
- GSC site URL missing → `SearchConsoleController` shows config alert, no API call
- GSC API error (auth, quota) → caught, logged, returns empty data + flash warning
- Widget data fetch failure → individual widget shows "Veri yüklenemedi" placeholder, other widgets unaffected

---

## 6. Testing

- `SearchConsoleServiceTest` — unit test with mocked HTTP client: valid response, empty response, auth error
- `DashboardWidgetTest` — feature test: save layout, load layout, per-user isolation
- `AnalyticsControllerTest` — feature test: overview metrics returned, % change calculation correct, previous period window correct
- `SearchConsoleControllerTest` — feature test: index returns view, fetch returns JSON, unauthenticated redirects

---

## 7. File Summary

### New files
- `app/Services/GoogleSearchConsoleService.php`
- `app/Services/DashboardWidgetService.php`
- `app/Http/Controllers/Admin/SearchConsoleController.php`
- `app/Models/DashboardWidget.php`
- `resources/views/panel/search-console.blade.php`
- `resources/views/panel/widgets/` (18 partial files)
- `database/migrations/YYYY_MM_DD_create_dashboard_widgets_table.php`
- `tests/Feature/SearchConsoleControllerTest.php`
- `tests/Feature/DashboardWidgetTest.php`
- `tests/Unit/SearchConsoleServiceTest.php`

### Modified files
- `app/Actions/GoogleIndexingAction.php` — read from file, not DB
- `app/Http/Controllers/Admin/DashboardController.php` — add widget loading + saveWidgets
- `app/Http/Controllers/Admin/AnalyticsController.php` — add overview metrics + previous period
- `resources/views/panel/analytics.blade.php` — add cards + trend chart
- `resources/views/panel/dashboard.blade.php` — gridstack widget system
- `resources/views/panel/settings/index.blade.php` — remove credentials upload
- `app/Http/Controllers/Admin/Settings/GeneralSettingsController.php` — remove credentials save
- `resources/views/panel/base.blade.php` — add GSC sidebar link
- `routes/panel/` — add GSC + widget save routes
