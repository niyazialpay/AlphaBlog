<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        try {
            return response()->view('themes.'.app('theme')->name.'.home', ['category' => null]);
        } catch (Exception $e) {
            return response()->view('Default.home', ['category' => null]);
        }
    }
}
