<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Panel\PanelResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use PDO;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AboutController extends Controller
{
    public function index(): Response
    {
        $systemInfo = [
            'Application Name' => config('app.name'),
            'PHP Version' => phpversion(),
            'Laravel Version' => app()->version(),
            'MySQL Version' => $this->databaseVersion(),
            'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'Server IP Address' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
            'Server Port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
            'Operating System' => php_uname(),
            'Hostname' => gethostname(),
            'Timezone' => config('app.timezone'),
            'Locale' => app()->getLocale(),
            'Default Charset' => ini_get('default_charset'),
            'Cache Driver' => config('cache.default'),
            'Session Driver' => config('session.driver'),
            'Session Lifetime' => config('session.lifetime').' minutes',
            'Queue Driver' => config('queue.default'),
            'Queue Connection' => Queue::getConnectionName(),
            'Filesystem Driver' => config('filesystems.default'),
            'Mail Driver' => config('mail.default'),
            'Mail Host' => config('mail.mailers.'.config('mail.default').'.host'),
            'Mail Port' => config('mail.mailers.'.config('mail.default').'.port'),
            'Environment' => config('app.env'),
            'Memory Limit' => ini_get('memory_limit'),
            'Upload Max Filesize' => ini_get('upload_max_filesize'),
            'Post Max Size' => ini_get('post_max_size'),
            'Max Execution Time' => ini_get('max_execution_time').' seconds',
            'Composer Autoload' => base_path('vendor/autoload.php'),
            'Storage Path' => storage_path(),
            'PHP Extensions Loaded' => implode(', ', get_loaded_extensions()),
            'OpenSSL Version' => OPENSSL_VERSION_TEXT,
        ];

        /*
         * 'Debug Mode' artik HTML rozet stringi degil bool prop.
         * Not: bu ekran php_uname(), sunucu IP'si ve yuklu PHP eklentilerini
         * tasiyor. Blade'de server-render'di, Inertia'da data-page JSON'una girer;
         * route can('admin') ile korunuyor ve bunlarin HICBIRI paylasilan
         * prop'a konmamali.
         */
        return PanelResponse::render(
            'About/Index',
            'panel.about',
            ['debug' => (bool) config('app.debug'), 'systemInfo' => $systemInfo],
            ['systemInfo' => $this->legacySystemInfo($systemInfo)],
        );
    }

    /**
     * Surucuden bagimsiz veritabani surumu.
     *
     * Onceki hali `DB::select('SELECT VERSION()')` idi; MySQL disinda
     * (ornegin test ortamindaki sqlite) ekrani 500'e dusuruyordu.
     */
    private function databaseVersion(): string
    {
        try {
            return (string) DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (Throwable) {
            return 'Unknown';
        }
    }

    /**
     * Eski Blade ekrani {!! !!} ile bastigi icin rozet HTML'ini bekliyor.
     * PANEL_UI=blade geri donusu bozulmasin diye burada yeniden uretilir.
     *
     * @param  array<string, mixed>  $systemInfo
     * @return array<string, mixed>
     */
    private function legacySystemInfo(array $systemInfo): array
    {
        return ['Debug Mode' => config('app.debug')
            ? '<span class="badge bg-danger">Enabled</span>'
            : '<span class="badge bg-success">Disabled</span>',
        ] + $systemInfo;
    }
}
