<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

class AboutController extends Controller
{
    public function index(){
        $systemInfo = [
            'Debug Mode' => config('app.debug') ? '<span class="badge bg-danger">Enabled</span>' : '<span class="badge bg-success">Disabled</span>',
            'Application Name' => config('app.name'),
            'PHP Version' => phpversion(),
            'Laravel Version' => app()->version(),
            'MySQL Version' => DB::select("SELECT VERSION() as version")[0]->version,
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
            'Session Lifetime' => config('session.lifetime') . ' minutes',
            'Queue Driver' => config('queue.default'),
            'Queue Connection' => Queue::getConnectionName(),
            'Filesystem Driver' => config('filesystems.default'),
            'Mail Driver' => config('mail.default'),
            'Mail Host' => config('mail.mailers.' . config('mail.default') . '.host'),
            'Mail Port' => config('mail.mailers.' . config('mail.default') . '.port'),
            'Environment' => config('app.env'),
            'Memory Limit' => ini_get('memory_limit'),
            'Upload Max Filesize' => ini_get('upload_max_filesize'),
            'Post Max Size' => ini_get('post_max_size'),
            'Max Execution Time' => ini_get('max_execution_time') . ' seconds',
            'Composer Autoload' => base_path('vendor/autoload.php'),
            'Storage Path' => storage_path(),
            'PHP Extensions Loaded' => implode(', ', get_loaded_extensions()),
            'OpenSSL Version' => OPENSSL_VERSION_TEXT,
        ];

        return view('panel.about', compact('systemInfo'));
    }
}
