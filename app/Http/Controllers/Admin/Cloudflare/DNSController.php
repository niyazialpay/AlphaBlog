<?php

namespace App\Http\Controllers\Admin\Cloudflare;

use App\Http\Controllers\Controller;
use App\Models\Cloudflare;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DNSController extends Controller
{
    public static string $zoneID;

    public static DNS $dns;

    private bool $invalidCredentials = false;

    public function __construct()
    {
        $cf = Cloudflare::first();

        if ($cf) {
            $key = new APIKey($cf->cf_email, $cf->cf_key);
            $adapter = new Guzzle($key);
            $zones = new Zones($adapter);
            self::$dns = new DNS($adapter);
            try {
                self::$zoneID = $zones->getZoneID($cf->domain);
            } catch (Exception $e) {
                $this->invalidCredentials = true;
            }
        } else {
            $this->invalidCredentials = true;
        }
    }

    public function index()
    {
        if ($this->invalidCredentials) {
            return redirect()->route('admin.settings', ['tab' => 'cloudflare']);
        }

        $cf = Cloudflare::first();
        if (! $cf) {
            return redirect()->route('admin.settings', ['tab' => 'cloudflare']);
        }

        return view('panel.cloudflare.dns');
    }

    public function dns_json(Request $request): JsonResponse
    {
        $columns = [
            0 => 'proxied',
            1 => 'type',
            2 => 'name',
            3 => 'content',
            4 => 'ttl',
        ];

        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (! empty($request->input('search.value'))) {
            $search = $request->input('search.value');
        } else {
            $search = '';
        }

        $count = count(self::$dns->listRecords(self::$zoneID, name: $search, perPage: 5000, match: 'any')->result);

        $data = [
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => [],
        ];

        $k = 0;
        if ($count > 0) {
            foreach (self::$dns->listRecords(self::$zoneID, name: $search, perPage: 5000, order: $order, direction: $dir, match: 'any')->result as $record) {
                //echo $record->name." ".$record->type." ".$record->content." | Record ID ".$record->id."<br>".PHP_EOL;
                if ($record->proxied == 1) {
                    $status = '<span class="cloud" style="background: transparent url('.config('app.url').'/themes/panel/img/cficon.png) 0 -83px no-repeat;"></span>';
                } else {
                    $status = '<span class="cloud" style="background: transparent url('.config('app.url').'/themes/panel/img/cficon.png) 0 -150px no-repeat;"></span>';
                }
                if ($record->type == 'MX') {
                    $content = $record->priority.' '.$record->content;
                } else {
                    $content = $record->content;
                }
                if ($record->ttl == 1) {
                    $ttl = 'Auto';
                } else {
                    $ttl = $record->ttl;
                }
                $vdata = [
                    'id' => $record->id,
                    'type' => $record->type,
                    'content' => $content,
                    'name' => $record->name,
                    'ttl' => $ttl,
                    'status' => $status,
                    'proxied' => $record->proxied,
                    'all_data' => $record,
                    'action' => '<a href="javascript:void(0);" class="edit btn btn-primary"><i class="fa fa-edit"></i></a> <a href="javascript:void(0);" class="delete btn btn-danger"><i class="fa fa-trash"></i></a>',
                ];
                $data['data'][$k] = $vdata;
                $k++;
            }
        }

        return response()->json($data);
    }

    public function create_edit(Request $request): JsonResponse
    {
        $type = $request->post('record_type');
        if ($type == 'A' || $type == 'AAAA' || $type == 'CNAME') {
            if ($request->post('status')) {
                if ($request->post('status') == 1) {
                    $proxied = true;
                } else {
                    $proxied = false;
                }
            } else {
                $proxied = false;
            }
            $data = [
                'type' => $type,
                'name' => $request->post('name'),
                'content' => $request->post('content'),
                'ttl' => (int) $request->post('ttl', 1),
                'proxied' => $proxied,
                'priority' => '',
            ];
        } elseif ($type == 'MX') {
            $data = [
                'type' => $type,
                'name' => $request->post('name'),
                'content' => $request->post('content'),
                'priority' => intval($request->post('priority')),
                'ttl' => (int) $request->post('ttl', 1),
                'proxied' => false,
            ];
        } elseif ($type == 'TXT') {
            $data = [
                'type' => $type,
                'name' => $request->post('name'),
                'content' => $request->post('content'),
                'ttl' => (int) $request->post('ttl', 1),
                'proxied' => false,
                'priority' => '',
            ];
        } elseif ($type == 'SRV') {
            $data = [
                'type' => $type,
                'name' => $request->post('service').'.'.$request->post('protocol').'.'.$request->post('name'),
                'content' => $request->post('name'),
                'data' => [
                    'priority' => $request->post('priority'),
                    'service' => $request->post('service'),
                    'proto' => $request->post('protocol'),
                    'weight' => $request->post('weight'),
                    'port' => $request->post('port'),
                    'target' => $request->post('target'),
                    'name' => $request->post('name'),
                ],
                'ttl' => (int) $request->post('ttl', 1),
                'proxied' => false,
                'priority' => $request->post('priority'),
            ];
        } elseif ($type == 'CAA') {
            $data = [
                'type' => $type,
                'name' => $request->post('name'),
                'content' => $request->post('content'),
                'data' => [
                    'flags' => $request->post('flags'),
                    'tag' => $request->post('tag'),
                    'value' => $request->post('content'),
                ],
                'ttl' => (int) $request->post('ttl', 1),
                'proxied' => false,
                'priority' => '',
            ];
        } elseif ($type == 'HTTPS') {
            $data = [
                'type' => $type,
                'name' => $request->post('name'),
                'content' => $request->post('content'),
                'data' => [
                    'priority' => $request->post('priority'),
                    'target' => $request->post('target'),
                    'value' => $request->post('content'),
                ],
                'ttl' => (int) $request->post('ttl', 1),
                'proxied' => false,
                'priority' => '',
            ];
        } else {
            $data = [
                'type' => $type,
                'name' => $request->post('name'),
                'content' => $request->post('content'),
                'ttl' => (int) $request->post('ttl', 1),
                'proxied' => false,
                'priority' => '',
            ];
        }

        if ($request->post('type') == 'add') {
            self::$dns->addRecord(self::$zoneID, $data['type'], $data['name'], $data['content'], $data['ttl'], $data['proxied'], priority: $data['priority'], data: $data['data'] ?? []);
        } elseif ($request->post('type') == 'edit') {
            self::$dns->updateRecordDetails(self::$zoneID, $request->post('dns_id'), $data);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Invalid type']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'DNS record has been added successfully',
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        self::$dns->deleteRecord(self::$zoneID, $request->post('dns_id'));

        return response()->json([
            'status' => 'success',
            'message' => 'DNS record has been deleted successfully',
        ]);
    }
}
