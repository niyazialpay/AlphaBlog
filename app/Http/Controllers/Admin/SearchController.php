<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalNotes\PersonalNotes;
use App\Models\Post\Categories;
use App\Models\Post\Posts;
use App\Models\Search;
use App\Models\User;
use Exception;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Search $search, Request $request)
    {
        return view('panel.search', [
            'search' => $search::where('search', 'like', '%'.$request->search.'%')
                ->orderBy('think', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20),
            'browser' => new Browser(),
        ]);
    }

    public function check(Search $search, Request $request)
    {
        $data = $search::where('search', 'like', '%'.$request->search.'%')
            ->orderBy('think', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        foreach ($data as $item) {
            $item->update([
                'checked' => true,
            ]);
            $item->save();
        }

        return response()->json([
            'status' => 'success',
            'list' => $data,
        ]);
    }

    public function delete(Search $search)
    {
        try {
            DB::beginTransaction();
            if ($search->delete()) {
                DB::commit();

                return response()->json([
                    'status' => true,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function think(Search $search)
    {
        $search->update([
            'think' => ! $search->think,
        ]);
        if ($search->save()) {
            return response()->json([
                'status' => true,
                'think' => $search->think,
                'message' => __('search.think.updated'),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => __('search.think.error'),
            ]);
        }
    }

    public function deleteAll()
    {
        try {
            DB::beginTransaction();
            if (Search::truncate()) {
                DB::commit();

                return response()->json([
                    'status' => true,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function deleteNotThink()
    {
        try {
            DB::beginTransaction();
            if (Search::where('think', false)->delete()) {
                DB::commit();

                return response()->json([
                    'status' => true,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('search');

        $paginate = 10;
        $results = [];

        $results['posts'] = Posts::search($query)->take($paginate)->where('post_type', 'post')->orderBy('created_at', 'desc')->get();
        $results['page'] = Posts::search($query)->take($paginate)->where('post_type', 'page')->orderBy('created_at', 'desc')->get();
        $results['categories'] = Categories::search($query)->orderBy('created_at', 'desc')->take($paginate)->get();
        $results['users'] = User::search($query)->orderBy('created_at', 'desc')->take($paginate)->get();
        if (! request()->cookie('encryption_key')) {
            $personal_notes = new PersonalNotes();
            $personal_notes::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
            $results['personal_notes'] = $personal_notes->search($query)->orderBy('created_at', 'desc')->take($paginate)->get();
        }
        else{
            $results['personal_notes'] = [];
        }

        return response()->json($results);
    }
}
