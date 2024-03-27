<?php

namespace App\Http\Controllers;

use App\Mail\Contact;
use App\Models\ContactPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index($language, $contact)
    {
        return view('themes.'.app('theme')->name.'.contact', [
            'contact' => ContactPage::where('language', $language)->first()
        ]);
    }

    private function emailSend($request){
        $send = Mail::send(new Contact([
            'name' => $request->name,
            'subject' => $request->subject,
            'email' => $request->email,
            'message' => $request->message
        ]));
        if($send) {
            return true;
        }
        else{
            return false;
        }
    }
    public function send(Request $request){
        if($this->emailSend($request)) {
            return redirect()->back()->with('success', __('contact.mail.success'));
        }
        else{
            return redirect()->back()->with('error', __('contact.mail.error'));
        }
    }

    function send_ajax(Request $request){
        if($this->emailSend($request)) {
            return response()->json(['message' => __('contact.mail.success')]);
        }
        else{
            return response()->json(['message' => __('contact.mail.error')]);
        }
    }
}
