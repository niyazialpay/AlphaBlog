<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use Illuminate\Http\Request;
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

        if ($request->has('order.0.name')) {
            $order = $request->input('order.0.name');
        } else {
            $order = 'created_at';
        }

        if ($request->has('order.0.dir')) {
            $dir = $request->input('order.0.dir');
        } else {
            $dir = 'desc';
        }

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
                return '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            })
            ->addColumn('new_data', function (Logs $log) {
                $json = json_decode($log->new_data, true);
                return '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            })
            ->addColumn('action', function (Logs $log) {
                return __('logs.action_list.'.$log->action);
            })
            ->rawColumns(['old_data', 'new_data'])
            ->toJson();
    }
}
