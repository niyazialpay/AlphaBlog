<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        foreach (preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
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

        $users = $query->orderBy('name')->orderBy('surname')->limit(self::LIMIT)->get();

        return response()->json([
            'data' => $users->map(fn (User $user) => self::option($user, $includeEmail))->values(),
        ]);
    }

    /**
     * @return array{value: string, label: string, description: string|null}
     */
    public static function option(User $user, bool $includeEmail = false): array
    {
        $fullName = trim($user->name.' '.$user->surname);
        $label = match (true) {
            $fullName !== '' && filled($user->nickname) => "{$fullName} ({$user->nickname})",
            $fullName !== '' => $fullName,
            filled($user->nickname) => (string) $user->nickname,
            default => (string) $user->email,
        };

        return [
            'value' => (string) $user->id,
            'label' => $label,
            'description' => $includeEmail ? $user->email : null,
        ];
    }
}
