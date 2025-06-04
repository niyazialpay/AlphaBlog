<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;

class AuthorsController extends Controller
{
    public function index()
    {
        $authors = User::withCount('posts')
            ->with('social')
            ->whereNot('role', 'user')
            ->paginate();

        try {
            return response()->view('themes.' . app('theme')->name . '.authors', [
                'authors' => $authors,
            ]);
        } catch (Exception $e) {
            return response()->view('Default.authors', [
                'authors' => $authors,
            ]);
        }
    }
}
