<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function posts($language, $user, User $users)
    {
        return view(app('theme')->name . '.user-posts', [
            'user' => $users
        ]);
    }
}
