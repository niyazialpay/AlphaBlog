# Analytics + GSC + Dashboard Widgets Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Analytics overview metrics with % changes, new Search Console page, and per-user customizable gridstack dashboard.

**Architecture:** GSC credential unification (single service-account JSON for both GA4 and GSC), new GoogleSearchConsoleService, SearchConsoleController + view, DashboardWidget model + migration, DashboardWidgetService registry, 18 widget partials, gridstack.js dashboard with modal widget picker and per-user persistence.

**Tech Stack:** Laravel 12, Spatie Analytics v5.3, google/auth v1.50 (ServiceAccountCredentials), ApexCharts, gridstack.js CDN, AdminLTE Blade, PHPUnit

---

## File Map

### New Files
- `app/Services/GoogleSearchConsoleService.php`
- `app/Services/DashboardWidgetService.php`
- `app/Http/Controllers/Admin/SearchConsoleController.php`
- `app/Models/DashboardWidget.php`
- `database/migrations/2026_05_21_000001_create_dashboard_widgets_table.php`
- `resources/views/panel/search-console.blade.php`
- `resources/views/panel/widgets/ga4_active_users.blade.php`
- `resources/views/panel/widgets/ga4_new_users.blade.php`
- `resources/views/panel/widgets/ga4_pageviews.blade.php`
- `resources/views/panel/widgets/ga4_events.blade.php`
- `resources/views/panel/widgets/ga4_engagement_time.blade.php`
- `resources/views/panel/widgets/ga4_visitors_trend.blade.php`
- `resources/views/panel/widgets/ga4_browsers.blade.php`
- `resources/views/panel/widgets/ga4_countries.blade.php`
- `resources/views/panel/widgets/ga4_os.blade.php`
- `resources/views/panel/widgets/ga4_user_types.blade.php`
- `resources/views/panel/widgets/ga4_top_pages.blade.php`
- `resources/views/panel/widgets/gsc_clicks.blade.php`
- `resources/views/panel/widgets/gsc_impressions.blade.php`
- `resources/views/panel/widgets/gsc_ctr.blade.php`
- `resources/views/panel/widgets/gsc_position.blade.php`
- `resources/views/panel/widgets/gsc_keywords.blade.php`
- `resources/views/panel/widgets/site_comments.blade.php`
- `resources/views/panel/widgets/site_firewall.blade.php`
- `tests/Feature/SearchConsoleControllerTest.php`
- `tests/Feature/DashboardWidgetTest.php`
- `tests/Unit/SearchConsoleServiceTest.php`

### Modified Files
- `app/Actions/GoogleIndexingAction.php` — read credentials from file, not DB
- `app/Http/Controllers/Admin/AnalyticsController.php` — overview metrics + previous period
- `app/Http/Controllers/Admin/DashboardController.php` — widget loading + saveWidgets
- `app/Models/User.php` — dashboardWidgets() HasMany relationship
- `resources/views/panel/analytics.blade.php` — overview cards + trend comparison chart
- `resources/views/panel/dashboard.blade.php` — gridstack widget system
- `resources/views/panel/settings/index.blade.php` — remove credentials upload UI
- `app/Http/Controllers/Admin/Settings/SeoSettingsController.php` — remove credential save
- `resources/views/panel/partials/menu.blade.php` — add GSC sidebar link
- `routes/panel/panel.php` — add GSC + widget routes
- `tests/Feature/GoogleIndexingActionTest.php` — update for file-based credentials

---

## Task 1: GSC Credential Unification

**Files:**
- Modify: `app/Actions/GoogleIndexingAction.php`
- Modify: `resources/views/panel/settings/index.blade.php` (lines 422–443 remove)
- Modify: `app/Http/Controllers/Admin/Settings/SeoSettingsController.php` (lines 72–99 remove credential save)
- Modify: `tests/Feature/GoogleIndexingActionTest.php`

- [ ] **Step 1: Update GoogleIndexingAction to read from file**

Replace both `submit()` and `inspect()` credential reads. Change:
```php
$settings = GeneralSettings::first();
$credentialsJson = $settings?->google_indexing_credentials;

if (! $credentialsJson) {
    Log::warning('Google Indexing: credentials not configured.');
    return ['status' => 'failed', 'code' => 0, 'message' => 'Google indexing credentials not configured.'];
}

$credentials = json_decode($credentialsJson, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    Log::warning('Google Indexing: invalid credentials JSON.');
    return ['status' => 'failed', 'code' => 0, 'message' => 'Invalid credentials JSON.'];
}
```
To:
```php
$credentialsPath = storage_path('app/analytics/service-account-credentials.json');

if (! file_exists($credentialsPath)) {
    Log::warning('Google Indexing: service-account-credentials.json not found.');
    return ['status' => 'failed', 'code' => 0, 'message' => 'Google indexing credentials not configured.'];
}

$credentials = json_decode(file_get_contents($credentialsPath), true);
```

Same pattern for `inspect()`. Remove the `GeneralSettings` import and use from `submit()` (but keep it in `inspect()` for `google_indexing_site_url`). Also remove `use App\Models\Settings\GeneralSettings;` only if it's no longer needed — `dailyQuotaReached()` still uses it, so keep the import.

- [ ] **Step 2: Remove credential upload UI from settings view**

In `resources/views/panel/settings/index.blade.php`, remove lines 422–443 (the credential file upload + textarea inputs). Keep the `google_indexing_enabled`, `google_indexing_daily_limit`, and `google_indexing_site_url` fields. Add a note: `<div class="form-text text-info">Servis hesabı kimlik bilgileri: <code>storage/app/analytics/service-account-credentials.json</code></div>`

- [ ] **Step 3: Remove credential save logic from SeoSettingsController**

In `saveGoogleIndexing()`, remove lines that handle `google_indexing_credentials_file` and `google_indexing_credentials` (lines 72–99). Keep save of `google_indexing_enabled`, `google_indexing_daily_limit`, `google_indexing_site_url` only.

- [ ] **Step 4: Update GoogleIndexingActionTest**

Update `test_submit_returns_failed_when_no_credentials` to not depend on DB credentials. Instead, test that when the file does not exist, submit() returns failed. Use `Storage::fake()` or a temp path approach. Actually: just ensure that the file path `storage_path('app/analytics/service-account-credentials.json')` doesn't exist in the test environment (it won't), so the test should work as-is after updating the assertion. Remove the `GeneralSettings::first()->update(['google_indexing_credentials' => null]);` line since credentials come from file now, not DB.

- [ ] **Step 5: Run pint and tests**

```bash
./vendor/bin/pint app/Actions/GoogleIndexingAction.php app/Http/Controllers/Admin/Settings/SeoSettingsController.php --format agent
php artisan test --compact --filter=GoogleIndexingActionTest
```

- [ ] **Step 6: Commit**

```bash
git add app/Actions/GoogleIndexingAction.php resources/views/panel/settings/index.blade.php app/Http/Controllers/Admin/Settings/SeoSettingsController.php tests/Feature/GoogleIndexingActionTest.php
git commit -m "feat: unify GSC credentials to use service-account-credentials.json"
```

---

## Task 2: Analytics Overview Metrics + Trend Chart

**Files:**
- Modify: `app/Http/Controllers/Admin/AnalyticsController.php`
- Modify: `resources/views/panel/analytics.blade.php`

- [ ] **Step 1: Add overview metrics to AnalyticsController**

Add private method `fetchOverviewMetrics(Carbon $start, Carbon $end): array` that:
1. Calls `Analytics::get(Period::create($start, $end), ['activeUsers','newUsers','screenPageViews','eventCount','userEngagementDuration','sessions'])` - no dimensions
2. Gets first row from result collection, casts all values to int/float
3. Computes previous period: `$prevEnd = $start->copy()->subDay(); $prevStart = $prevEnd->copy()->subDays($start->diffInDays($end));`
4. Fetches same metrics for previous period
5. Returns array: `['active_users' => ['value' => N, 'change' => +12.4], 'new_users' => [...], ...]`
6. % change formula: `$prev > 0 ? round(($curr - $prev) / $prev * 100, 1) : null`
7. For engagement time: `$curr_time = ($curr_sessions > 0) ? round($curr_dur / $curr_sessions) : 0;` then format as "Xm Ys"
8. Cache per `{prefix}analytics_overview_{startDate}_{endDate}` for 15 min

Add call to this method in `extracted()`, merge `overview` key into returned array. Wrap in `file_exists` check — return `overview` as empty array fallback.

- [ ] **Step 2: Add previous period visitors trend to AnalyticsController**

Add private method `fetchTrendData(Carbon $start, Carbon $end): array` that calls `Analytics::get()` with `['activeUsers','screenPageViews']` dimensions `['date']`. Returns array with date-keyed activeUsers values.

In `extracted()`, add `'trend_current'` and `'trend_previous'` keys.

- [ ] **Step 3: Update analytics.blade.php — Overview Cards**

Add before the existing date range picker row:
```html
@if(!empty($overview))
<div class="row mb-3">
    <!-- 5 metric cards: active_users, new_users, pageviews, events, engagement_time -->
    <!-- Each card: label, value, badge with ▲/▼ + % -->
</div>
@endif
```

- [ ] **Step 4: Update analytics.blade.php — Trend Chart**

Add after overview cards, before existing charts:
```html
<div class="col-12">
    <div class="card radius-10">
        <div class="card-header">Aktif Kullanıcı Trendi</div>
        <div class="card-body">
            <div id="overview_trend_chart"></div>
        </div>
    </div>
</div>
```

In the script section, initialize an ApexCharts line chart with two series (current period solid, previous dashed). Update on AJAX refresh. Add to dark mode switcher handler.

- [ ] **Step 5: Update AJAX handler in analytics.blade.php**

The `fetchDataAndUpdateCharts()` success handler must also update overview cards (replace innerHTML of each metric value/badge) and the trend chart. Pass overview and trend data back from controller in JSON.

- [ ] **Step 6: Run pint and tests**

```bash
./vendor/bin/pint app/Http/Controllers/Admin/AnalyticsController.php --format agent
php artisan test --compact --filter=AnalyticsController
```

- [ ] **Step 7: Commit**

```bash
git add -f app/Http/Controllers/Admin/AnalyticsController.php resources/views/panel/analytics.blade.php
git commit -m "feat: add analytics overview metrics with period-over-period % changes and trend chart"
```

---

## Task 3: GoogleSearchConsoleService

**Files:**
- Create: `app/Services/GoogleSearchConsoleService.php`

- [ ] **Step 1: Create service class**

```php
<?php
namespace App\Services;

use App\Models\Settings\GeneralSettings;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSearchConsoleService
{
    private const SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';
    private const ENDPOINT = 'https://searchconsole.googleapis.com/webmasters/v3/sites/{siteUrl}/searchAnalytics/query';

    private function getToken(): ?string — reads service-account file, creates ServiceAccountCredentials, returns access_token

    private function getSiteUrl(): ?string — reads from GeneralSettings::first()->google_indexing_site_url

    public function getPerformance(Carbon $startDate, Carbon $endDate): array
    // Returns ['current' => [...totals], 'previous' => [...totals]]
    // current/previous each: ['clicks' => N, 'impressions' => N, 'ctr' => F, 'position' => F]

    public function getKeywords(Carbon $startDate, Carbon $endDate, int $limit = 50): array
    // Returns array of ['query' => str, 'clicks' => N, 'impressions' => N, 'ctr' => F, 'position' => F]

    public function getClicksTrend(Carbon $startDate, Carbon $endDate): array
    // Returns array of ['date' => 'YYYY-MM-DD', 'clicks' => N, 'impressions' => N]
}
```

- File not found → return empty/fallback without exception
- Site URL not set → return empty/fallback without exception  
- API error → caught, logged, return empty/fallback

- [ ] **Step 2: Run pint**

```bash
./vendor/bin/pint app/Services/GoogleSearchConsoleService.php --format agent
```

- [ ] **Step 3: Commit**

```bash
git add -f app/Services/GoogleSearchConsoleService.php
git commit -m "feat: add GoogleSearchConsoleService for GSC Search Analytics API"
```

---

## Task 4: SearchConsoleController + Routes + View + Sidebar

**Files:**
- Create: `app/Http/Controllers/Admin/SearchConsoleController.php`
- Create: `resources/views/panel/search-console.blade.php`
- Modify: `routes/panel/panel.php`
- Modify: `resources/views/panel/partials/menu.blade.php`

- [ ] **Step 1: Create SearchConsoleController**

```php
class SearchConsoleController extends Controller
{
    public function index(Request $request): View
    public function fetch(Request $request): JsonResponse
    private function getData(Request $request): array
}
```

`getData()`:
- Parses date range same pattern as AnalyticsController
- Checks if file and site URL exist; if not, returns `['configured' => false]`
- Caches per `{prefix}gsc_{startDate}_{endDate}` for 30 min
- Returns `['configured' => true, 'performance' => [...], 'keywords' => [...], 'trend' => [...], 'date_range' => '...']`

`index()` → passes data to `panel.search-console` view
`fetch()` → returns `response()->json($this->getData($request))`

- [ ] **Step 2: Add routes to panel.php**

After the existing analytics routes, add:
```php
Route::get('/search-console', [App\Http\Controllers\Admin\SearchConsoleController::class, 'index'])
    ->name('admin.search-console')->can('admin', 'App\Models\User');

Route::post('/search-console/fetch', [App\Http\Controllers\Admin\SearchConsoleController::class, 'fetch'])
    ->name('admin.search-console.fetch')->can('admin', 'App\Models\User');
```

- [ ] **Step 3: Add GSC link to sidebar menu**

In `resources/views/panel/partials/menu.blade.php`, after the analytics `</li>` (after line 25), add:
```blade
@can('admin', 'App\Models\User')
    <li class="nav-item">
        <a href="{{route('admin.search-console')}}" class="nav-link
    @if(request()->is(config('settings.admin_panel_path').'/search-console')) active @endif ">
            @if(config('settings.fontawesome_pro'))
            <i class="fa-duotone fa-magnifying-glass-chart nav-icon"></i>
            @else
                <i class="fa-solid fa-magnifying-glass-chart nav-icon"></i>
            @endif
            <p>
                Search Console
            </p>
        </a>
    </li>
@endcan
```

- [ ] **Step 4: Create search-console.blade.php**

Structure mirrors analytics.blade.php:
1. `@extends('panel.base')`
2. Date range picker (same JS as analytics)
3. If `!$configured`: show `<div class="alert alert-info">Search Console entegrasyonu yapılandırılmamış...</div>`
4. 4 summary cards (Toplam Tıklama, Toplam Gösterim, Ort. CTR, Ort. Konum) with % change badges
5. ApexCharts dual-Y-axis line chart: clicks (green, left) + impressions (blue, right)
6. Keywords table with client-side filter, sortable columns, position badge coloring (1-3 green, 4-10 yellow, 11+ red)

Dark mode support: `theme: { mode: dashboard_theme_mode }` on all charts.

AJAX refresh: same pattern as analytics with POST to `admin.search-console.fetch`.

- [ ] **Step 5: Run pint**

```bash
./vendor/bin/pint app/Http/Controllers/Admin/SearchConsoleController.php --format agent
```

- [ ] **Step 6: Commit**

```bash
git add -f app/Http/Controllers/Admin/SearchConsoleController.php resources/views/panel/search-console.blade.php routes/panel/panel.php resources/views/panel/partials/menu.blade.php
git commit -m "feat: add Google Search Console page with keyword performance and trend chart"
```

---

## Task 5: DashboardWidget Migration + Model + User Relationship

**Files:**
- Create: `database/migrations/2026_05_21_000001_create_dashboard_widgets_table.php`
- Create: `app/Models/DashboardWidget.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1: Create migration**

```php
Schema::create('dashboard_widgets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('widget_type', 60);
    $table->tinyInteger('gs_x')->default(0);
    $table->tinyInteger('gs_y')->default(0);
    $table->tinyInteger('gs_w')->default(3);
    $table->tinyInteger('gs_h')->default(2);
    $table->json('settings')->nullable();
    $table->timestamps();
    $table->index('user_id');
});
```

- [ ] **Step 2: Create DashboardWidget model**

```php
class DashboardWidget extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'widget_type', 'gs_x', 'gs_y', 'gs_w', 'gs_h', 'settings'];
    protected function casts(): array { return ['settings' => 'array']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
```

- [ ] **Step 3: Add dashboardWidgets() to User model**

```php
public function dashboardWidgets(): HasMany
{
    return $this->hasMany(DashboardWidget::class)->orderBy('gs_y')->orderBy('gs_x');
}
```

- [ ] **Step 4: Run migration**

Note to subagent: Do NOT run migrate on a remote server. Run it in local dev only if available. Just create the files — the user will run `php artisan migrate` manually.

- [ ] **Step 5: Run pint**

```bash
./vendor/bin/pint app/Models/DashboardWidget.php app/Models/User.php --format agent
```

- [ ] **Step 6: Commit**

```bash
git add -f database/migrations/2026_05_21_000001_create_dashboard_widgets_table.php app/Models/DashboardWidget.php app/Models/User.php
git commit -m "feat: add DashboardWidget model, migration, and User relationship"
```

---

## Task 6: DashboardWidgetService

**Files:**
- Create: `app/Services/DashboardWidgetService.php`

- [ ] **Step 1: Create service**

```php
class DashboardWidgetService
{
    public const WIDGETS = [
        'ga4_active_users'    => ['label' => 'Aktif Kullanıcı',       'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_new_users'       => ['label' => 'Yeni Kullanıcı',        'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_pageviews'       => ['label' => 'Sayfa Görüntüleme',     'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_events'          => ['label' => 'Etkinlik Sayısı',       'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_engagement_time' => ['label' => 'Ort. Etkileşim',        'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_visitors_trend'  => ['label' => 'Ziyaretçi Trendi',      'w' => 6, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_browsers'        => ['label' => 'Tarayıcılar',           'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_countries'       => ['label' => 'Ülkeler',               'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_os'              => ['label' => 'İşletim Sistemleri',    'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_user_types'      => ['label' => 'Kullanıcı Tipi',        'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_top_pages'       => ['label' => 'En Çok Görüntülenen',   'w' => 6, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'gsc_clicks'          => ['label' => 'GSC Tıklama',           'w' => 3, 'h' => 2, 'group' => 'Search Console'],
        'gsc_impressions'     => ['label' => 'GSC Gösterim',          'w' => 3, 'h' => 2, 'group' => 'Search Console'],
        'gsc_ctr'             => ['label' => 'GSC CTR',               'w' => 3, 'h' => 2, 'group' => 'Search Console'],
        'gsc_position'        => ['label' => 'GSC Ort. Konum',        'w' => 3, 'h' => 2, 'group' => 'Search Console'],
        'gsc_keywords'        => ['label' => 'Top Keywords',          'w' => 12,'h' => 5, 'group' => 'Search Console'],
        'site_comments'       => ['label' => 'Son Yorumlar',          'w' => 6, 'h' => 4, 'group' => 'Site'],
        'site_firewall'       => ['label' => 'Firewall Logları',      'w' => 6, 'h' => 4, 'group' => 'Site'],
    ];

    public function getDataForWidgets(Collection $widgets): array
    // Only fetch data needed for active widget types
    // GA4 data: if any ga4_* widget → fetch with AnalyticsController logic (7-day period)
    // GSC data: if any gsc_* widget → fetch with GoogleSearchConsoleService (7-day period)  
    // Site data: always available (comments + firewall from DB)
    // Returns ['ga4' => [...], 'gsc' => [...], 'comments' => [...], 'firewall' => [...]]
    // Cached per user ID for 15 min

    public static function widgetGroups(): array
    // Returns WIDGETS grouped by 'group' key for the modal
}
```

- [ ] **Step 2: Run pint**

```bash
./vendor/bin/pint app/Services/DashboardWidgetService.php --format agent
```

- [ ] **Step 3: Commit**

```bash
git add -f app/Services/DashboardWidgetService.php
git commit -m "feat: add DashboardWidgetService with widget registry and data fetching"
```

---

## Task 7: Widget Blade Partials (18 files)

**Files:** `resources/views/panel/widgets/*.blade.php`

Each partial receives `$widgetData` array from DashboardWidgetService. Each must:
- Be a self-contained card (no extra outer div — gridstack wraps it)
- Show "Veri yüklenemedi" placeholder if its data key is missing/empty
- Use AdminLTE card classes

### GA4 Metric Cards (5 files: ga4_active_users, ga4_new_users, ga4_pageviews, ga4_events, ga4_engagement_time)

Each:
```blade
<div class="card h-100 mb-0">
  <div class="card-body p-3">
    <div class="text-muted small">{{ $label }}</div>
    <div class="h3 mb-0">{{ $value }}</div>
    @if($change !== null)
    <span class="badge badge-{{ $change >= 0 ? 'success' : 'danger' }} mt-1">
        {{ $change >= 0 ? '▲' : '▼' }} {{ abs($change) }}%
    </span>
    @endif
  </div>
</div>
```

### GA4 Chart Widgets (6 files: ga4_visitors_trend, ga4_browsers, ga4_countries, ga4_os, ga4_user_types, ga4_top_pages)

Each: card with unique chart div ID (using `widget_{{ $widget->id ?? uniqid() }}`), inline ApexCharts init script. Accept empty data gracefully.

### GSC Metric Cards (4 files: gsc_clicks, gsc_impressions, gsc_ctr, gsc_position)

Same structure as GA4 metric cards but pulling from `$widgetData['gsc']['performance']`.

### gsc_keywords (1 file)

Small table showing top 10 keywords with link to full GSC page. Columns: Kelime | Tıklama | Konum.

### site_comments (1 file)

Table showing last 5 comments with user, post, date. Link to full comments page.

### site_firewall (1 file)

Table showing last 5 firewall logs with IP, reason, date. Link to full firewall logs page.

- [ ] **Step: Create all 18 partials**
- [ ] **Step: Commit**

```bash
git add -f resources/views/panel/widgets/
git commit -m "feat: add 18 dashboard widget blade partials"
```

---

## Task 8: Dashboard Widget UI — DashboardController + dashboard.blade.php + Routes

**Files:**
- Modify: `app/Http/Controllers/Admin/DashboardController.php`
- Modify: `resources/views/panel/dashboard.blade.php`
- Modify: `routes/panel/panel.php`

- [ ] **Step 1: Update DashboardController**

```php
use App\Models\DashboardWidget;
use App\Services\DashboardWidgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

public function index(): View
{
    $user = auth()->user();
    $widgets = $user->dashboardWidgets;
    $widgetData = (new DashboardWidgetService)->getDataForWidgets($widgets);
    $widgetGroups = DashboardWidgetService::widgetGroups();
    
    return view('panel.dashboard', compact('widgets', 'widgetData', 'widgetGroups'));
}

public function saveWidgets(Request $request): JsonResponse
{
    $layout = $request->validate(['layout' => 'required|array']);
    $user = auth()->user();
    
    DashboardWidget::where('user_id', $user->id)->delete();
    
    foreach ($layout['layout'] as $item) {
        DashboardWidget::create([
            'user_id' => $user->id,
            'widget_type' => $item['type'],
            'gs_x' => (int) $item['x'],
            'gs_y' => (int) $item['y'],
            'gs_w' => (int) $item['w'],
            'gs_h' => (int) $item['h'],
        ]);
    }
    
    return response()->json(['status' => 'success']);
}
```

Remove the old GA4 data fetching from `index()` (now handled by DashboardWidgetService).

Keep `changeLanguage()` as-is.

- [ ] **Step 2: Add routes to panel.php**

```php
Route::post('/dashboard/widgets', [App\Http\Controllers\Admin\DashboardController::class, 'saveWidgets'])
    ->name('admin.dashboard.widgets.save')->can('admin', 'App\Models\User');
```

- [ ] **Step 3: Rewrite dashboard.blade.php**

Complete rewrite of the `@section('content')` and `@section('script')` blocks. Keep `@extends('panel.base')` etc.

Content section:
```blade
@can('admin', 'App\Models\User')
<div class="row mb-2">
    <div class="col-12 d-flex justify-content-end gap-2">
        @if($widgets->isNotEmpty())
        <button id="edit-toggle-btn" class="btn btn-sm btn-secondary">
            <i class="fas fa-edit me-1"></i> Düzenle
        </button>
        @endif
        <button id="add-widget-btn" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#widgetModal">
            <i class="fas fa-plus me-1"></i> Widget Ekle
        </button>
    </div>
</div>

@if($widgets->isEmpty())
    {{-- empty state --}}
    <div class="row">
        <div class="col-12 text-center py-5">
            <i class="fas fa-th-large fa-3x text-muted mb-3"></i>
            <p class="text-muted">Dashboard henüz boş.</p>
            <button class="btn btn-primary" data-toggle="modal" data-target="#widgetModal">
                <i class="fas fa-plus me-1"></i> Widget Ekle
            </button>
        </div>
    </div>
@else
    {{-- gridstack container --}}
    <div class="grid-stack">
        @foreach($widgets as $widget)
        <div class="grid-stack-item"
             gs-x="{{ $widget->gs_x }}"
             gs-y="{{ $widget->gs_y }}"
             gs-w="{{ $widget->gs_w }}"
             gs-h="{{ $widget->gs_h }}"
             data-widget-type="{{ $widget->widget_type }}">
            <div class="grid-stack-item-content">
                @include('panel.widgets.'.$widget->widget_type, ['widgetData' => $widgetData, 'widget' => $widget])
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- Widget Library Modal --}}
<div class="modal fade" id="widgetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Widget Ekle</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                @foreach($widgetGroups as $group => $items)
                <h6 class="text-uppercase text-muted small font-weight-bold mb-2">{{ $group }}</h6>
                <div class="row mb-3">
                    @foreach($items as $type => $config)
                    <div class="col-6 col-md-4 mb-2">
                        <button class="btn btn-outline-secondary btn-block text-left add-widget-item"
                                data-type="{{ $type }}"
                                data-w="{{ $config['w'] }}"
                                data-h="{{ $config['h'] }}">
                            {{ $config['label'] }}
                        </button>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endcan
```

Script section:
```javascript
// Load gridstack CSS+JS from CDN
// Initialize grid with disableDrag:true, disableResize:true
// "Düzenle" button toggles edit mode (grid.enable() / grid.disable(), show/hide × buttons)
// "Widget Ekle" modal: clicking item → grid.addWidget({x,y,w,h, content: '<div>...</div>'})
//   then AJAX GET /admin/dashboard/widget/{type} to load actual widget HTML into cell
// Auto-save: on grid 'change' event, debounce 500ms, POST to admin.dashboard.widgets.save
//   with layout JSON: [{type, x, y, w, h}, ...]
// Remove widget: × button calls grid.removeWidget(), triggers save
```

Actually: widget HTML loading can be done via server-side since we pass `$widgetData` to the view. When adding a new widget from modal, we need to reload the page or make an AJAX call to get the widget HTML. Simplest approach: after clicking "Widget Ekle" from modal, POST to save, then reload page. This avoids the complexity of AJAX-loading widget HTML.

Alternative: just POST to save with the new widget, then window.location.reload(). This is simpler and more reliable.

- [ ] **Step 4: Run pint**

```bash
./vendor/bin/pint app/Http/Controllers/Admin/DashboardController.php --format agent
```

- [ ] **Step 5: Commit**

```bash
git add -f app/Http/Controllers/Admin/DashboardController.php resources/views/panel/dashboard.blade.php routes/panel/panel.php
git commit -m "feat: replace dashboard with per-user gridstack widget system"
```

---

## Task 9: Tests

**Files:**
- Create: `tests/Feature/SearchConsoleControllerTest.php`
- Create: `tests/Feature/DashboardWidgetTest.php`
- Create: `tests/Unit/SearchConsoleServiceTest.php`
- Also update: `tests/Feature/AnalyticsControllerTest.php` if it exists, or create it

- [ ] **Step 1: Create SearchConsoleControllerTest**

```php
// test_index_returns_view_when_configured
// test_index_shows_not_configured_alert_when_no_file
// test_fetch_returns_json
// test_unauthenticated_redirects
```

- [ ] **Step 2: Create DashboardWidgetTest**

```php
// test_user_starts_with_empty_dashboard
// test_save_widgets_stores_widgets_for_user
// test_save_widgets_replaces_existing
// test_widgets_are_isolated_per_user (user A's widgets don't show for user B)
// test_unauthenticated_redirects
```

- [ ] **Step 3: Create SearchConsoleServiceTest (unit)**

```php
// test_returns_empty_when_credentials_file_missing
// test_returns_empty_when_site_url_not_set
```

- [ ] **Step 4: Run all tests**

```bash
php artisan test --compact
```

- [ ] **Step 5: Commit**

```bash
git add -f tests/
git commit -m "test: add SearchConsoleController, DashboardWidget, and SearchConsoleService tests"
```
