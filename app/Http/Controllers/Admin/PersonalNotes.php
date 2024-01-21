<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PersonalNotes extends Controller
{
    public function index(){
        return view('panel.personal_notes.index', [
            'notes' => auth()->user()->notes()->paginate(10),
        ]);
    }
}
