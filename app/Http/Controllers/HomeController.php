<?php

namespace App\Http\Controllers;

use App\Models\Themes;
use Exception;
use Illuminate\Http\Request;
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
        try{
            return response()->view(app('theme').'.home');
        }
        catch (Exception $e){
            return response()->view('Default.home');
        }
    }
}
