<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Support\Panel\PanelResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class LogsController extends Controller
{
    /**
     * Sunucu tarafli tablo artik AYRI bir JSON beslemesi degil, Inertia prop'u.
     *
     * Gerekcesi:
     *   - assertInertia ile test edilebilir (JSON beslemesi degildi),
     *   - versiyonlama / ?format= bayragi / kopya metot gerekmez,
     *   - Vue tarafi kismi yeniden yukleme (only: ['logs','filters']) kullandigi
     *     icin URL durumu paylasilabilir ve geri tusu dogru calisir.
     *
     * Eski `admin.system-logs.data` ucu DOKUNULMADAN duruyor: henuz tasinmamis
     * Blade ekrani ve olasi dis cagiranlar icin.
     */
    public function index(Request $request): Response
    {
        $perPage = self::perPage($request);
        $search = trim((string) $request->get('search'));
        $sort = self::sortColumn($request);
        $direction = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $logs = Logs::with('user')
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                foreach (['ip', 'user_agent', 'model', 'action', 'old_data', 'new_data'] as $column) {
                    $q->orWhere($column, 'like', '%'.$search.'%');
                }
            }))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return PanelResponse::render(
            'Logs/Index',
            'panel.logs.index',
            [
                'logs' => PanelResponse::rows($logs, fn (Logs $log) => [
                    'id' => $log->id,
                    'ip' => $log->ip,
                    'user_agent' => $log->user_agent,
                    'model' => $log->model,
                    // HAM anahtar gonderiliyor, cevrilmis metin degil:
                    // boylece eyleme gore arama da dogru calisir.
                    'action' => $log->action,
                    // Gercek nesne; eski uc '<pre>'.htmlspecialchars(...) basiyordu.
                    'old_data' => json_decode((string) $log->old_data, true),
                    'new_data' => json_decode((string) $log->new_data, true),
                    'user' => $log->user ? ['id' => $log->user->id, 'nickname' => $log->user->nickname] : null,
                    'createdAt' => $log->created_at?->toIso8601String(),
                ]),
                'filters' => [
                    'search' => $search !== '' ? $search : null,
                    'sort' => $sort,
                    'dir' => $direction,
                    'per_page' => $perPage,
                ],
            ],
            [],
        );
    }

    /**
     * Sayfa boyu, eski tablolarin paylastigi session anahtari yerine acik bir
     * query parametresi. jQuery DataTables'in lengthMenu degerleriyle sinirli.
     */
    private static function perPage(Request $request): int
    {
        $perPage = (int) $request->get('per_page', 10);

        return in_array($perPage, [10, 25, 50, 75, 100], true) ? $perPage : 10;
    }

    private static function sortColumn(Request $request): string
    {
        $sort = (string) $request->get('sort', 'created_at');

        return Schema::hasColumn((new Logs)->getTable(), $sort) ? $sort : 'created_at';
    }

    public function logsData(Request $request)
    {
        $query = Logs::with('user');

        $order = (string) $request->input('order.0.name', 'created_at');
        if (! Schema::hasColumn((new Logs)->getTable(), $order)) {
            $order = 'created_at';
        }
        $dir = strtolower((string) $request->input('order.0.dir')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($order, $dir);

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->get('search')['value']) {
                    $search = $request->get('search')['value'];
                    $query->where('ip', 'like', "%$search%")
                        ->orWhere('user_agent', 'like', "%$search%")
                        ->orWhere('model', 'like', "%$search%")
                        ->orWhere('action', 'like', "%$search%")
                        ->orWhere('old_data', 'like', "%$search%")
                        ->orWhere('new_data', 'like', "%$search%")
                        ->orWhere('created_at', 'like', "%$search%");
                }
            })
            ->addColumn('user', function (Logs $log) {
                return $log->user ? $log->user->nickname : '';
            })
            ->addColumn('created_at', function (Logs $log) {
                return $log->created_at->format('d.m.Y H:i:s');
            })
            ->addColumn('old_data', function (Logs $log) {
                $json = json_decode($log->old_data, true);

                return '<pre>'.htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre>';
            })
            ->addColumn('new_data', function (Logs $log) {
                $json = json_decode($log->new_data, true);

                return '<pre>'.htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</pre>';
            })
            ->addColumn('action', function (Logs $log) {
                return __('logs.action_list.'.$log->action);
            })
            ->rawColumns(['old_data', 'new_data'])
            ->toJson();
    }
}
