<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class LogsController extends Controller
{
    public function index()
    {
        return view('panel.logs.index');
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
