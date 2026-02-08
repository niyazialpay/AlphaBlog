<?php

namespace Tests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AppModelRelationsFeatureTest extends TestCase
{
    #[DataProvider('modelProvider')]
    public function test_app_model_can_be_bootstrapped(string $modelClass): void
    {
        $model = new $modelClass;

        $this->assertInstanceOf(Model::class, $model);
        $this->assertIsString($model->getTable());
        $this->assertNotSame('', trim($model->getTable()));
    }

    #[DataProvider('relationProvider')]
    public function test_app_model_relationship_methods_return_relations(string $modelClass, string $relationMethod): void
    {
        $relation = (new $modelClass)->{$relationMethod}();

        $this->assertInstanceOf(Relation::class, $relation);
    }

    public static function modelProvider(): array
    {
        return [
            [\App\Models\AdminOneSignal::class],
            [\App\Models\Cloudflare::class],
            [\App\Models\ContactMessages::class],
            [\App\Models\ContactPage::class],
            [\App\Models\Firewall\Firewall::class],
            [\App\Models\Firewall\FirewallLogs::class],
            [\App\Models\IPFilter\IPFilter::class],
            [\App\Models\IPFilter\IPList::class],
            [\App\Models\IPFilter\RouteList::class],
            [\App\Models\Languages::class],
            [\App\Models\Logs::class],
            [\App\Models\Menu\Menu::class],
            [\App\Models\Menu\MenuItems::class],
            [\App\Models\OneSignal::class],
            [\App\Models\PersonalNotes\PersonalNoteCategories::class],
            [\App\Models\PersonalNotes\PersonalNotes::class],
            [\App\Models\Post\Categories::class],
            [\App\Models\Post\Comments::class],
            [\App\Models\Post\PostCategory::class],
            [\App\Models\Post\PostHistory::class],
            [\App\Models\Post\Posts::class],
            [\App\Models\ProfilePrivacy::class],
            [\App\Models\RouteRedirects::class],
            [\App\Models\Search::class],
            [\App\Models\Session::class],
            [\App\Models\Settings\AdvertiseSettings::class],
            [\App\Models\Settings\AnalyticsSettings::class],
            [\App\Models\Settings\GeneralSettings::class],
            [\App\Models\Settings\SeoSettings::class],
            [\App\Models\Settings\SocialSettings::class],
            [\App\Models\SocialNetworks::class],
            [\App\Models\Themes::class],
            [\App\Models\User::class],
            [\App\Models\UserSessions::class],
            [\App\Models\WebAuthnCredential::class],
        ];
    }

    public static function relationProvider(): array
    {
        return [
            [\App\Models\WebAuthnCredential::class, 'user'],
            [\App\Models\User::class, 'notes'],
            [\App\Models\User::class, 'social'],
            [\App\Models\User::class, 'posts'],
            [\App\Models\User::class, 'noteCategories'],
            [\App\Models\User::class, 'WebAuthn'],
            [\App\Models\User::class, 'privacy'],
            [\App\Models\User::class, 'sessions'],
            [\App\Models\UserSessions::class, 'user'],
            [\App\Models\UserSessions::class, 'session'],
            [\App\Models\PersonalNotes\PersonalNotes::class, 'user'],
            [\App\Models\PersonalNotes\PersonalNotes::class, 'category'],
            [\App\Models\SocialNetworks::class, 'user'],
            [\App\Models\Firewall\FirewallLogs::class, 'ipFilter'],
            [\App\Models\Firewall\FirewallLogs::class, 'ipList'],
            [\App\Models\PersonalNotes\PersonalNoteCategories::class, 'user'],
            [\App\Models\PersonalNotes\PersonalNoteCategories::class, 'notes'],
            [\App\Models\Logs::class, 'user'],
            [\App\Models\Menu\Menu::class, 'menuItems'],
            [\App\Models\Menu\MenuItems::class, 'menu'],
            [\App\Models\Menu\MenuItems::class, 'children'],
            [\App\Models\IPFilter\RouteList::class, 'filter'],
            [\App\Models\ProfilePrivacy::class, 'user'],
            [\App\Models\IPFilter\IPFilter::class, 'ipList'],
            [\App\Models\IPFilter\IPFilter::class, 'routeList'],
            [\App\Models\IPFilter\IPList::class, 'filter'],
            [\App\Models\Post\PostCategory::class, 'post'],
            [\App\Models\Post\PostCategory::class, 'category'],
            [\App\Models\Post\PostHistory::class, 'post'],
            [\App\Models\Post\Posts::class, 'user'],
            [\App\Models\Post\Posts::class, 'categories'],
            [\App\Models\Post\Posts::class, 'comments'],
            [\App\Models\Post\Posts::class, 'history'],
            [\App\Models\Post\Posts::class, 'commentCount'],
            [\App\Models\Post\Posts::class, 'postMedia'],
            [\App\Models\Post\Categories::class, 'posts'],
            [\App\Models\Post\Categories::class, 'parent'],
            [\App\Models\Post\Categories::class, 'children'],
            [\App\Models\Post\Categories::class, 'CategoryPosts'],
            [\App\Models\Post\Categories::class, 'categoryMedia'],
            [\App\Models\Post\Comments::class, 'user'],
            [\App\Models\Post\Comments::class, 'post'],
        ];
    }
}
