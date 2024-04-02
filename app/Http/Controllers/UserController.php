<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function posts($language, $user, User $users)
    {
        return view('themes.'.app('theme')->name.'.user-posts', [
            'user' => $users,
        ]);
    }
}
