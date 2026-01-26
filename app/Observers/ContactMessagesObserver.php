<?php

namespace App\Observers;

use App\Mail\Contact;
use App\Models\ContactMessages;
use Illuminate\Support\Facades\Mail;

class ContactMessagesObserver
{
    public function created(ContactMessages $contactMessage): void
    {
        $function = config('settings.mail_send_method') === 'directly' ? 'send' : 'queue';

        Mail::$function(new Contact([
            'name' => $contactMessage->name,
            'subject' => $contactMessage->subject,
            'email' => $contactMessage->email,
            'message' => $contactMessage->message,
        ]));
    }
}
