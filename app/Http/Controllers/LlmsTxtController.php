<?php

namespace App\Http\Controllers;

use App\Models\Languages;
use App\Models\Post\Categories;
use App\Models\Post\Posts;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class LlmsTxtController extends Controller
{
    private const CACHE_KEY = 'llms_txt_content';

    private const CACHE_KEY_FULL = 'llms_full_txt_content';

    public function index(): Response
    {
        $content = Cache::rememberForever(self::CACHE_KEY, fn () => $this->build(false));

        return response($content)->header('Content-Type', 'text/plain; charset=utf-8');
    }

    public function full(): Response
    {
        $content = Cache::rememberForever(self::CACHE_KEY_FULL, fn () => $this->build(true));

        return response($content)->header('Content-Type', 'text/plain; charset=utf-8');
    }

    private function build(bool $full): string
    {
        $languages = Languages::all();
        $primaryLanguage = $languages->firstWhere('is_default', true) ?? $languages->first();
        $seoSettings = SeoSettings::where('language', $primaryLanguage?->code)->first();
        $generalSettings = GeneralSettings::first();

        $lines = [];

        $siteName = $seoSettings?->site_name ?? config('app.name');
        $lines[] = "# {$siteName}";
        $lines[] = '';

        if ($intro = $generalSettings?->llms_txt_intro) {
            $lines[] = $intro;
            $lines[] = '';
        }

        if ($instructions = $generalSettings?->llms_txt_instructions) {
            $lines[] = $instructions;
            $lines[] = '';
        }

        $allPosts = Posts::query()
            ->published()
            ->where('post_type', 'post')
            ->where('created_at', '<=', now())
            ->latest()
            ->get(['id', 'title', 'slug', 'meta_description', 'content', 'language']);

        foreach ($languages as $language) {
            $langPosts = $allPosts->where('language', $language->code)->values();

            if ($langPosts->isEmpty()) {
                continue;
            }

            $lines[] = "## {$language->name} Content";

            foreach ($langPosts as $post) {
                $url = url("/{$language->code}/{$post->slug}");
                $excerpt = $this->excerpt($post->meta_description ?: ($post->content ?? ''));
                $line = "- [{$post->title}]({$url})";
                if ($excerpt !== '') {
                    $line .= ": {$excerpt}";
                }
                $lines[] = $line;
            }

            $lines[] = '';

            if ($full) {
                foreach ($langPosts as $post) {
                    $url = url("/{$language->code}/{$post->slug}");
                    $lines[] = "### {$post->title}";
                    $lines[] = "URL: {$url}";
                    $lines[] = '';
                    $lines[] = strip_tags($post->content ?? '');
                    $lines[] = '';
                    $lines[] = '---';
                    $lines[] = '';
                }
            }
        }

        $categories = Categories::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        if ($categories->isNotEmpty()) {
            $lines[] = '## Categories';

            foreach ($categories as $category) {
                foreach ($languages as $language) {
                    $url = url("/{$language->code}/categories/{$category->slug}");
                    $lines[] = "- [{$category->name} ({$language->code})]({$url})";
                }
            }

            $lines[] = '';
        }

        $pages = Posts::query()
            ->published()
            ->where('post_type', 'page')
            ->where('created_at', '<=', now())
            ->latest()
            ->get(['title', 'slug', 'meta_description', 'language']);

        if ($pages->isNotEmpty()) {
            $lines[] = '## Pages';

            foreach ($pages as $page) {
                $url = url("/{$page->language}/{$page->slug}");
                $excerpt = $this->excerpt($page->meta_description ?? '');
                $line = "- [{$page->title}]({$url})";
                if ($excerpt !== '') {
                    $line .= ": {$excerpt}";
                }
                $lines[] = $line;
            }

            $lines[] = '';
        }

        $moduleStatusPath = base_path('modules_statuses.json');
        if (file_exists($moduleStatusPath)) {
            $statuses = json_decode(file_get_contents($moduleStatusPath), true) ?? [];
            $activeModules = array_keys(array_filter($statuses));

            if (! empty($activeModules)) {
                $lines[] = '## Optional';
                foreach ($activeModules as $module) {
                    $lines[] = "- {$module}";
                }
                $lines[] = '';
            }
        }

        return implode("\n", $lines);
    }

    private function excerpt(string $text, int $max = 120): string
    {
        $text = strip_tags($text);
        $text = (string) preg_replace('/\s+/', ' ', trim($text));

        if ($text === '') {
            return '';
        }

        return mb_strlen($text) <= $max ? $text : mb_substr($text, 0, $max).'...';
    }
}
