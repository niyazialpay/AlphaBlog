<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FirewallController extends Controller
{
    public function index()
    {
        return view('admin.firewall.index');
    }

    public function logs()
    {
        return view('admin.firewall.logs');
    }
}
