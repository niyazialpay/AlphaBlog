<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalNotes\PersonalNotes;
use App\Models\Post\Categories;
use App\Models\Post\Posts;
use App\Models\Search;
use App\Models\User;
use App\Support\Panel\PanelResponse;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SearchController extends Controller
{
    public function index(Search $search, Request $request): Response
    {
        $words = $search::where('search', 'like', '%'.$request->search.'%')
            ->orderBy('think', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return PanelResponse::render(
            'SearchWords/Index',
            'panel.search',
            [
                /*
                 * Blade'e `new Browser` ornegi gecirilip iceride statik olarak
                 * cagriliyordu ($browser::platformName(...)). Servis nesnesi
                 * JSON'a serilestirilemez; cozumleme burada yapilip string
                 * gonderiliyor.
                 */
                'words' => PanelResponse::rows($words, fn (Search $item) => [
                    'id' => $item->id,
                    'search' => $item->search,
                    'checked' => (bool) $item->checked,
                    'think' => (bool) $item->think,
                    'ip' => $item->ip,
                    'platform' => Browser::platformName($item->user_agent),
                    'browser' => Browser::browserName($item->user_agent),
                    'createdAt' => $item->created_at?->toIso8601String(),
                ]),
                'filters' => ['search' => $request->search],
                'total' => $words->total(),
            ],
            ['search' => $words, 'browser' => new Browser],
        );
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

        // Tek cagiran resources/js/panel/Pages/SearchWords/Index.vue, router.post
        // ile Inertia uzerinden cagiriyor - eski jQuery/axios tuketicisi yok.
        return back()->with('success', __('search.check.success'));
    }

    public function delete(Search $search, Request $request)
    {
        try {
            DB::beginTransaction();
            if ($search->delete()) {
                DB::commit();

                // R2: Inertia yonlendirme alir; diger cagiranlar icin sekil degismez.
                return $request->inertia()
                    ? back()->with('success', __('search.delete.success'))
                    : response()->json(['status' => true]);
            }

            // Bu yol commit GORMUYOR: transaction acik kalirsa baglanti
            // istek boyunca kilit tutar.
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', __('search.delete.error'))
                : response()->json(['status' => false]);
        } catch (Throwable $e) {
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', __('search.delete.error'))
                : response()->json(['status' => false]);
        }
    }

    public function think(Search $search, Request $request)
    {
        /*
         * Korumasiz: bos/eslesmeyen bagli modelde `update()` var olmayan satira
         * INSERT denemesi yapiyor ve firlatiyordu (500). Kayit yoksa 404 dogru
         * yanittir.
         */
        if (! $search->exists) {
            abort(404);
        }

        $search->update([
            'think' => ! $search->think,
        ]);

        if ($search->save()) {
            return $request->inertia()
                ? back()->with('success', __('search.think.updated'))
                : response()->json([
                    'status' => true,
                    'think' => $search->think,
                    'message' => __('search.think.updated'),
                ]);
        }

        return $request->inertia()
            ? back()->with('error', __('search.think.error'))
            : response()->json([
                'status' => false,
                'message' => __('search.think.error'),
            ]);
    }

    public function deleteAll(Request $request)
    {
        try {
            DB::beginTransaction();
            if (Search::truncate()) {
                DB::commit();

                // R2: Inertia yonlendirme alir; diger cagiranlar icin sekil degismez.
                return $request->inertia()
                    ? back()->with('success', __('search.delete.success'))
                    : response()->json(['status' => true]);
            }

            // Bu yol commit GORMUYOR: transaction acik kalirdi.

            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', __('search.delete.error'))
                : response()->json(['status' => false]);
        } catch (Throwable $e) {
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', __('search.delete.error'))
                : response()->json(['status' => false]);
        }
    }

    public function deleteNotThink(Request $request)
    {
        try {
            DB::beginTransaction();
            if (Search::where('think', false)->delete()) {
                DB::commit();

                // R2: Inertia yonlendirme alir; diger cagiranlar icin sekil degismez.
                return $request->inertia()
                    ? back()->with('success', __('search.delete.success'))
                    : response()->json(['status' => true]);
            }

            // Bu yol commit GORMUYOR: transaction acik kalirdi.

            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', __('search.delete.error'))
                : response()->json(['status' => false]);
        } catch (Throwable $e) {
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', __('search.delete.error'))
                : response()->json(['status' => false]);
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
        if (request()->cookie('encryption_key')) {
            $personal_notes = new PersonalNotes;
            try {
                $personal_notes::encryptUsing(new Encrypter(request()->cookie('encryption_key'), Config::get('app.cipher')));
                $results['personal_notes'] = $personal_notes->search($query)->orderBy('created_at', 'desc')->take($paginate)->get();
            } catch (Throwable $e) {
                $results['personal_notes'] = [];
            }
        } else {
            $results['personal_notes'] = [];
        }

        return response()->json($results);
    }
}
