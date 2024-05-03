<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;

class UserController extends Controller
{
    public function posts($language, $user, User $users)
    {
        try {
            return view('themes.'.app('theme')->name.'.user-posts', [
                'user' => $users,
            ]);
        } catch (Exception $exception) {
            return view('Default.user-posts', [
                'user' => $users,
            ]);
        }
    }
}
