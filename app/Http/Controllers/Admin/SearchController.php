<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Search;
use Illuminate\Http\Request;
use hisorange\BrowserDetect\Parser as Browser;

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
            'list' => $data
        ]);
    }

    public function delete(Search $search)
    {
        if($search->delete()){
            return response()->json([
                'status' => true
            ]);
        }
        else{
            return response()->json([
                'status' => false
            ]);
        }
    }

    public function think(Search $search)
    {
        $search->update([
            'think' => !$search->think,
        ]);
        if($search->save()){
            return response()->json([
                'status' => true,
                'think' => $search->think,
                'message' => __('search.think.updated')
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => __('search.think.error')
            ]);
        }
    }

    public function deleteAll()
    {
        if(Search::truncate()){
            return response()->json([
                'status' => true
            ]);
        }
        else{
            return response()->json([
                'status' => false
            ]);

        }
    }

    public function deleteNotThink()
    {
        if(Search::where('think', false)->delete()){
            return response()->json([
                'status' => true
            ]);
        }
        else{
            return response()->json([
                'status' => false
            ]);
        }
    }

}
