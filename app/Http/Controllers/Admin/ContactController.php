<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactPage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function index()
    {
        return view('panel.contact', [
            'contactPage' => new ContactPage,
        ]);
    }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (app('languages') as $language) {
                $contact = ContactPage::where('language', $language->code)->first();
                if (! $contact) {
                    $contact = new ContactPage;
                    $contact->language = $language->code;
                }
                $contact->description = $request->input('description_'.$language->code);
                $contact->meta_description = $request->input('meta_description_'.$language->code);
                $contact->meta_keywords = $request->input('meta_keywords_'.$language->code);
                $contact->maps = $request->input('maps');
                $contact->save();
                Cache::forget(config('cache.prefix').'contact_page_'.$language->code);
            }
            DB::commit();

            return redirect()->back()->with('success', __('contact.saved'));
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
