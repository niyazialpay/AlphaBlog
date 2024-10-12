<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalNotes\PersonalNotesRequest;
use App\Models\PersonalNotes\PersonalNoteCategories;
use App\Models\PersonalNotes\PersonalNotes;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Queue\InvalidPayloadException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PersonalNotesController extends Controller
{
    public function index(Request $request, PersonalNotes $notes)
    {
        if (! request()->cookie('encryption_key')) {
            return view('panel.personal_notes.encryption-form');
        }
        $notes::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));

        $note = $notes->search($request->input('search'))
            ->query(function ($query) {
                $query->with('category');
            })
            ->where('user_id', auth()->id());

        if ($request->has('category') && $request->get('category') != '') {
            $note->where('category_id', $request->get('category'));
        }

        $notes = $note->orderBy('created_at', 'desc')
            ->paginate(10);

        try{
            foreach ($notes as $item) {
                $item->content;
            }
        }
        catch (Exception $e) {
            return view('panel.personal_notes.encryption-form');
        }

        return view('panel.personal_notes.index', [
            'notes' => $notes,
            'categories' => auth()->user()->noteCategories,
        ]);
    }

    public function show(PersonalNotes $note)
    {
        if (! request()->cookie('encryption_key')) {
            return view('panel.personal_notes.encryption-form');
        }
        $note::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
        try {
            $note->content;
        } catch (Exception $e) {
            abort(403, __('notes.encryption_key_invalid'));
        }

        $note = $note->load('category');

        try{
            $note->content;
        }
        catch (Exception $e) {
            return view('panel.personal_notes.encryption-form');
        }

        return view('panel.personal_notes.show', [
            'note' => $note,
        ]);
    }

    public function create(PersonalNotes $note)
    {
        if (! request()->cookie('encryption_key')) {
            return view('panel.personal_notes.encryption-form');
        }
        $note::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
        try {
            $note->content;
        } catch (Exception $e) {
            abort(403, __('notes.encryption_key_invalid'));
        }

        return view('panel.personal_notes.add-edit', [
            'note' => $note,
            'categories' => auth()->user()->noteCategories,
        ]);
    }

    public function save(PersonalNotesRequest $request, PersonalNotes $note)
    {
        try {
            DB::beginTransaction();
            $note::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
            $note->user_id = auth()->id();
            $note->title = $request->post('title');
            $note->content = $request->post('content');
            $note->category_id = $request->post('category_id');
            $note->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'id' => $note->id,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function editorImageUpload(PersonalNotes $note, Request $request)
    {
        try {
            DB::beginTransaction();
            if (! $note->id) {
                $note->title = GetPost($request->post('title')).' (draft)';
                $note->user_id = auth()->id();
                $note->save();
            }

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $note->addMediaFromRequest('file')->toMediaCollection('note_images');
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'note_id' => $note->id,
                'location' => $note->getMedia('note_images')->last()->getFullUrl('resized'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function postImageDelete(PersonalNotes $note, PersonalNotesRequest $request)
    {
        try {
            DB::beginTransaction();
            $note->deleteMedia($request->post('media_id'));
            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function media(PersonalNotes $note)
    {
        return view('panel.personal_notes.media', [
            'note' => $note,
        ]);
    }

    public function delete(PersonalNotes $note)
    {
        try {
            DB::beginTransaction();
            if ($note->delete()) {
                DB::commit();

                return response()->json(['status' => 'success', 'message' => __('personal_notes.success_delete')]);
            } else {
                return response()->json(['status' => 'error', 'message' => __('personal_notes.error_delete')]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function encryption(Request $request)
    {
        $request->validate([
            'encryption_key' => 'required',
            'remember_time' => 'required|integer|in:1,30,90,180,365',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => __('personal_notes.encryption_key_saved'),
        ])->withCookie(cookie('encryption_key',
            md5($request->post('encryption_key')),
            1440 * $request->post('remember_time'),
            null,
            null,
            true,
            true));
    }

    public function categories(PersonalNoteCategories $category)
    {
        if (! request()->cookie('encryption_key')) {
            return view('panel.personal_notes.encryption-form');
        }
        $category::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));

        return view('panel.personal_notes.categories.index', [
            'categories' => auth()->user()->noteCategories->load('notes'),
            'category' => $category,
        ]);
    }

    public function categorySave(Request $request, PersonalNoteCategories $category)
    {
        try {
            DB::beginTransaction();
            if (! request()->cookie('encryption_key')) {
                return view('panel.personal_notes.encryption-form');
            }
            $category::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));

            $request->validate([
                'name' => 'required',
            ]);
            $category->name = $request->post('name');
            $category->user_id = auth()->id();
            $category->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'id' => $category->id,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function categoryDelete(PersonalNoteCategories $category)
    {
        try {
            if ($category->notes->count() > 0) {
                return response()->json(['status' => false, 'message' => __('notes.error_delete_notes')]);
            }
            DB::beginTransaction();
            if ($category->delete()) {
                DB::commit();

                return response()->json(['status' => true, 'message' => __('notes.success_delete')]);
            } else {
                return response()->json(['status' => false, 'message' => __('notes.error_delete')]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
