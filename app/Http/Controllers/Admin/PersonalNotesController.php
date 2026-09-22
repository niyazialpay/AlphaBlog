<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalNotes\PersonalNotesRequest;
use App\Models\PersonalNotes\PersonalNoteCategories;
use App\Models\PersonalNotes\PersonalNotes;
use App\Support\Panel\PanelResponse;
use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PersonalNotesController extends Controller
{
    private function encryptionGate(): ?Response
    {
        if (request()->cookie('encryption_key')) {
            return null;
        }

        if (! request()->isMethodSafe()) {
            return to_route('admin.notes.categories')
                ->with('error', __('notes.encryption_key_required'));
        }

        return PanelResponse::render(
            'Notes/Encryption',
            'panel.personal_notes.encryption-form',
            ['intended' => request()->fullUrl()],
            [],
        );
    }

    private function invalidKeyGate(): Response
    {
        return PanelResponse::render(
            'Notes/Encryption',
            'panel.personal_notes.encryption-form',
            ['intended' => request()->fullUrl(), 'invalid' => true],
            [],
        );
    }

    public function index(Request $request, PersonalNotes $notes)
    {
        if ($gate = $this->encryptionGate()) {
            return $gate;
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

        try {
            foreach ($notes as $item) {
                $item->content;
            }
        } catch (Exception $e) {
            return $this->invalidKeyGate();
        }

        return PanelResponse::render(
            'Notes/Index',
            'panel.personal_notes.index',
            [
                'notes' => PanelResponse::rows($notes, fn (PersonalNotes $item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'category' => $item->category ? ['id' => $item->category->id, 'name' => $item->category->name] : null,
                    'createdAt' => $item->created_at?->toIso8601String(),
                ]),
                'categories' => auth()->user()->noteCategories
                    ->map(fn ($category) => ['id' => (string) $category->id, 'name' => $category->name])
                    ->values(),
                'filters' => [
                    'search' => $request->input('search'),
                    'category' => $request->get('category'),
                ],
            ],
            ['notes' => $notes, 'categories' => auth()->user()->noteCategories],
        );
    }

    public function show(PersonalNotes $note)
    {
        if ($gate = $this->encryptionGate()) {
            return $gate;
        }
        $note::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
        try {
            $note->content;
        } catch (Exception $e) {
            abort(403, __('notes.encryption_key_invalid'));
        }

        $note = $note->load('category');

        try {
            $note->content;
        } catch (Exception $e) {
            return $this->invalidKeyGate();
        }

        return PanelResponse::render(
            'Notes/Show',
            'panel.personal_notes.show',
            ['note' => $this->noteProps($note)],
            ['note' => $note],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function noteProps(PersonalNotes $note): array
    {
        return [
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'category_id' => $note->category_id ? (string) $note->category_id : '',
            'category' => $note->category ? ['id' => $note->category->id, 'name' => $note->category->name] : null,
            'createdAt' => $note->created_at?->toIso8601String(),
        ];
    }

    public function create(PersonalNotes $note)
    {
        if ($gate = $this->encryptionGate()) {
            return $gate;
        }
        $note::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
        try {
            $note->content;
        } catch (Exception $e) {
            abort(403, __('notes.encryption_key_invalid'));
        }

        return PanelResponse::render(
            'Notes/Edit',
            'panel.personal_notes.add-edit',
            [
                'note' => $this->noteProps($note),
                'categories' => auth()->user()->noteCategories
                    ->map(fn ($category) => ['id' => (string) $category->id, 'name' => $category->name])
                    ->values(),
            ],
            ['note' => $note, 'categories' => auth()->user()->noteCategories],
        );
    }

    public function save(PersonalNotesRequest $request, PersonalNotes $note): RedirectResponse
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

            return redirect()->route('admin.notes.edit', ['note' => $note->id])
                ->with('success', __('notes.success_save'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function editorImageUpload(PersonalNotes $note, Request $request)
    {
        $request->validate([
            'file' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
        ]);

        try {
            DB::beginTransaction();
            if (! $note->id) {
                $note->title = GetPost($request->post('title')).' (draft)';
                $note->user_id = auth()->id();
                $note->save();
            }

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $ext = $request->file('file')->extension();
                $note->addMediaFromRequest('file')
                    ->usingFileName(Str::random(40).'.'.$ext)
                    ->toMediaCollection('note_images');
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'note_id' => $note->id,
                'location' => mediaConversionUrl($note->getMedia('note_images')->last(), 'resized'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function postImageDelete(PersonalNotes $note, Request $request): RedirectResponse
    {
        $request->validate([
            'media_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $note->deleteMedia($request->post('media_id'));
            DB::commit();

            return back()->with('success', __('notes.media_deleted'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function media(PersonalNotes $note)
    {
        return PanelResponse::render(
            'Notes/Media',
            'panel.personal_notes.media',
            [
                'note' => ['id' => $note->id, 'title' => $note->title],
                'media' => $note->getMedia('note_images')
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->file_name,
                        'size' => $item->size,
                        'url' => $item->getFullUrl(),
                        'thumb' => mediaConversionUrl($item, 'resized'),
                    ])
                    ->values(),
            ],
            ['note' => $note],
        );
    }

    public function delete(PersonalNotes $note, Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            if ($note->delete()) {
                DB::commit();

                return back()->with('success', __('notes.deleted'));
            } else {
                DB::rollBack();

                return back()->with('error', __('notes.delete_error'));
            }
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function encryption(Request $request): RedirectResponse
    {
        $request->validate([
            'encryption_key' => 'required',
            'remember_time' => 'required|integer|in:1,30,90,180,365',
        ]);

        $response = back()->with('success', __('notes.encryption_key_saved'));

        return $response->withCookie(cookie('encryption_key',
            md5($request->post('encryption_key')),
            1440 * $request->post('remember_time'),
            null,
            null,
            true,
            true));
    }

    public function categories(PersonalNoteCategories $category)
    {
        if ($gate = $this->encryptionGate()) {
            return $gate;
        }
        $category::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));

        $categories = auth()->user()->noteCategories->load('notes');

        return PanelResponse::render(
            'Notes/Categories',
            'panel.personal_notes.categories.index',
            [
                'categories' => $categories->map(fn ($item) => [
                    'id' => (string) $item->id,
                    'name' => $item->name,
                    'notes_count' => $item->notes->count(),
                ])->values(),
                'category' => $category->id ? ['id' => (string) $category->id, 'name' => $category->name] : null,
            ],
            ['categories' => $categories, 'category' => $category],
        );
    }

    public function categorySave(Request $request, PersonalNoteCategories $category): Response
    {
        if ($gate = $this->encryptionGate()) {
            return $gate;
        }

        try {
            DB::beginTransaction();
            $category::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));

            $request->validate([
                'name' => 'required',
            ]);
            $category->name = $request->post('name');
            $category->user_id = auth()->id();
            $category->save();
            DB::commit();

            return back()->with('success', __('notes.success_save'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function categoryDelete(PersonalNoteCategories $category, Request $request): RedirectResponse
    {
        try {
            if ($category->notes->count() > 0) {
                return back()->with('error', __('notes.error_delete_notes'));
            }
            DB::beginTransaction();
            if ($category->delete()) {
                DB::commit();

                return back()->with('success', __('notes.success_delete'));
            } else {
                DB::rollBack();

                return back()->with('error', __('notes.error_delete'));
            }
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
}
