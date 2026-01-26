<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessages;

class ContactMessagesController extends Controller
{
    public function index()
    {
        $messages = ContactMessages::query()
            ->latest()
            ->paginate(10);

        return view('panel.contact-messages', [
            'messages' => $messages,
        ]);
    }
}
