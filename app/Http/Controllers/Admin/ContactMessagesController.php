<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessages;
use App\Support\Panel\PanelResponse;
use Symfony\Component\HttpFoundation\Response;

class ContactMessagesController extends Controller
{
    public function index(): Response
    {
        $messages = ContactMessages::query()
            ->latest()
            ->paginate(10);

        return PanelResponse::render(
            'Contact/Messages',
            'panel.contact-messages',
            [
                'messages' => PanelResponse::rows($messages, fn (ContactMessages $message) => [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'language' => $message->language,
                    'ip' => $message->ip_address,
                    'createdAt' => $message->created_at?->toIso8601String(),
                ]),
            ],
            ['messages' => $messages],
        );
    }
}
