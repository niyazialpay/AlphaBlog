<?php

namespace App\Http\Controllers;

use App\Models\ContactMessages;
use App\Models\ContactPage;
use App\Support\ThemeData;
use App\Support\ThemeManager;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index($language, $contact)
    {
        $contact = ContactPage::where('language', $language)->first();
        if (ThemeManager::usingVue()) {
            return ThemeManager::render('contact', [
                'contact' => ThemeData::contactPage($contact),
                'pageMeta' => ThemeData::metaForContact($contact),
            ]);
        }

        return ThemeManager::render('contact', [
            'contact' => $contact,
        ]);
    }

    private function storeMessage(Request $request): ContactMessages
    {
        return ContactMessages::create([
            'name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'email' => $request->input('email'),
            'message' => $request->input('message'),
            'language' => $request->route('language') ?? session('language'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function send(Request $request)
    {
        try {
            $this->storeMessage($request);
        } catch (\Throwable $exception) {
            return redirect()->back()->with('error', __('contact.mail.error'));
        }

        return redirect()->back()->with('success', __('contact.mail.success'));
    }

    public function send_ajax(Request $request)
    {
        try {
            $this->storeMessage($request);
        } catch (\Throwable $exception) {
            return response()->json(['message' => __('contact.mail.error')], 500);
        }

        return response()->json(['message' => __('contact.mail.success')]);
    }
}
