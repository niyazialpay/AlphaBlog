<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalNotes\PersonalNotesRequest;
use App\Models\PersonalNotes;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted;

class PersonalNotesController extends Controller
{
    public function index(Request $request, PersonalNotes $notes){
        return view('panel.personal_notes.index', [
            'notes' => $notes->search($request->input('search'))
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ]);
    }

    public function show(PersonalNotes $note){
        if(!request()->cookie('encryption_key')){
            abort(403, __('notes.encryption_key_required'));
        }
        $note::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
        try{
            $note->content;
        }
        catch (\Exception $e){
            abort(403, __('notes.encryption_key_invalid'));
        }
        return view('panel.personal_notes.show', [
            'note' => $note,
        ]);
    }

    public function create(PersonalNotes $note){
        if(!request()->cookie('encryption_key')){
            abort(403, __('notes.encryption_key_required'));
        }
        $note::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
        try{
            $note->content;
        }
        catch (\Exception $e){
            abort(403, __('notes.encryption_key_invalid'));
        }
        return view('panel.personal_notes.add-edit', [
            'note' => $note
        ]);
    }

    public function save(PersonalNotesRequest $request, PersonalNotes $note){
        $note::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
        $note->user_id = auth()->id();
        $note->title = $request->post('title');
        $note->content = $request->post('content');
        $note->save();
        return response()->json([
            'status' => 'success',
            'id' => $note->id,
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function editorImageUpload(PersonalNotes $note, PersonalNotesRequest $request)
    {
        if(!$note->_id){
            $note->title = GetPost($request->post('title'))." (draft)";
            $note->user_id = auth()->id();
            $note->save();
        }

        if($request->hasFile('file') && $request->file('file')->isValid()){
            $note->addMediaFromRequest('file')->toMediaCollection('note_images');
        }
        return response()->json([
            'success' => true,
            'note_id' => $note->id,
            'location' => $note->getMedia('note_images')->last()->getFullUrl('resized'),
        ]);
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function postImageDelete(PersonalNotes $note, PersonalNotesRequest $request)
    {
        $note->deleteMedia($request->post('media_id'));
        return response()->json([
            'success' => true,
        ]);
    }

    public function media(PersonalNotes $note)
    {
        return view('panel.personal_notes.media', [
            'note' => $note,
        ]);
    }

    public function delete(PersonalNotes $note){
        if($note->delete()){
            return response()->json(['status' => 'success', 'message' => __('personal_notes.success_delete')]);
        }else{
            return response()->json(['status' => 'error', 'message' => __('personal_notes.error_delete')]);
        }
    }

    public function encryption(Request $request){
        $request->validate([
            'encryption_key' => 'required',
        ]);
        return response()->json([
            'status' => 'success',
            'message' => __('personal_notes.encryption_key_saved'),
        ])->withCookie(cookie('encryption_key', md5($request->post('encryption_key')), 1440, null, null, true, true));
    }
}
