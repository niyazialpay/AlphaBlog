<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Yazi editorundeki "Yazar" alani icin SUNUCU TARAFLI kullanici aramasi.
 *
 * NEDEN: eskiden editore TUM kullanicilar liste olarak gonderiliyordu
 * (`User::all()`, sinirsiz) ve arama yalnizca o listenin takma adlari
 * uzerinde istemcide yapiliyordu. Ad/soyadla aramak mumkun degildi, buyuk bir
 * sitede de her editor acilisi tum kullanici tablosunu tasiyordu.
 *
 * VERI UCU (R1): JSON doner, istemci `axios` ile cagirir.
 *
 * GIZLILIK: e-posta hem ARAMA hem GOSTERIM icin yalnizca `admin` yetkisindeki
 * kullanicilara acik. Editor/yazar rolundeki birine tum kullanicilarin
 * e-postalarini gostermek veri sizintisi olurdu; e-postayla aramaya izin
 * vermek de "bu adres sitede kayitli mi" sorgusu yapmaya kapi acardi.
 */
class AuthorSearchController extends Controller
{
    private const LIMIT = 20;

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = trim((string) ($data['q'] ?? ''));
        $includeEmail = $request->user()->can('admin', User::class);

        $query = User::query()->select(['id', 'name', 'surname', 'nickname', 'email']);

        /*
         * Her KELIME ayri ayri eslesmeli: "ahmet riza" hem ad hem soyad
         * kolonlarina dagilmis olabilir. Tek bir `CONCAT(name, ' ', surname)`
         * yerine kelime basina kosul kullaniliyor — hem veritabani
         * bagimsiz (sqlite'ta CONCAT yok) hem de kelime sirasindan bagimsiz.
         */
        foreach (preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
            // `%` ve `_` kullanici girdisinde joker olarak yorumlanmasin.
            $like = '%'.addcslashes($word, '%_\\').'%';

            $query->where(function ($where) use ($like, $includeEmail) {
                $where->where('name', 'like', $like)
                    ->orWhere('surname', 'like', $like)
                    ->orWhere('nickname', 'like', $like);

                if ($includeEmail) {
                    $where->orWhere('email', 'like', $like);
                }
            });
        }

        $users = $query->orderBy('nickname')->limit(self::LIMIT)->get();

        return response()->json([
            'data' => $users->map(fn (User $user) => self::option($user, $includeEmail))->values(),
        ]);
    }

    /**
     * Secenek sekli; editor seed'i de ayni fonksiyonu kullanir ki etiket
     * sunucudan gelen arama sonucuyla birebir ayni olsun.
     *
     * @return array{value: string, label: string, description: string|null}
     */
    public static function option(User $user, bool $includeEmail = false): array
    {
        $fullName = trim($user->name.' '.$user->surname);
        $parts = array_filter([$fullName !== '' ? $fullName : null, $includeEmail ? $user->email : null]);

        return [
            'value' => (string) $user->id,
            'label' => (string) ($user->nickname ?: $fullName ?: $user->email),
            'description' => $parts ? implode(' · ', $parts) : null,
        ];
    }
}
