<?php

namespace App\Support\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Throwable;

final class NotificationEvents
{
    /**
     * @return array<string, array{label: string, ability: string|null, database: bool, push: bool}>
     */
    public static function all(): array
    {
        return [
            'comment.created' => [
                'label' => 'notifications.events.comment_created',
                'ability' => 'viewComments',
                'database' => true,
                'push' => true,
            ],
            'contact.message' => [
                'label' => 'notifications.events.contact_message',
                'ability' => 'admin',
                'database' => true,
                'push' => true,
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, ability: string|null, database: bool, push: bool}>
     */
    public static function forUser(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        return array_filter(
            self::all(),
            static function (array $event) use ($user): bool {
                if ($event['ability'] === null) {
                    return true;
                }

                try {
                    return Gate::forUser($user)->allows($event['ability']);
                } catch (Throwable) {
                    return false;
                }
            }
        );
    }

    public static function exists(string $event): bool
    {
        return array_key_exists($event, self::all());
    }

    /**
     * @return array{database: bool, push: bool}
     */
    public static function defaults(string $event): array
    {
        $definition = self::all()[$event] ?? null;

        return [
            'database' => $definition['database'] ?? true,
            'push' => $definition['push'] ?? false,
        ];
    }
}
