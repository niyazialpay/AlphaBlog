# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Alpha Blog (Niyazi.Net)** — A multi-language blogging/CMS platform built with Laravel 13, PHP 8.4+, MySQL, Vue 3 + Inertia.js, and Tailwind CSS. Features include AI chatbots (Gemini/ChatGPT), WebAuthn/2FA authentication, Meilisearch full-text search, modular architecture via nwidart/laravel-modules, and a configurable admin panel.

## Common Commands

```bash
# Development
php artisan serve                          # Backend server
npm run dev                                # Vite dev server (auto-installs theme deps)
npm run build                              # Production build

# Testing
php artisan test                           # Run all tests
php artisan test --filter=ClassName        # Run specific test
./vendor/bin/pint                          # PHP code formatting (PSR-12)

# Database & Search
php artisan migrate                        # Run migrations
php artisan scout:sync-index-settings      # Sync Meilisearch indexes

# Utilities
php artisan app:create-user                # Create user with role assignment
php artisan app:clear-trash                # Clean soft-deleted posts/comments, orphan search words
php artisan module:list                    # Check module status
php artisan optimize                       # Cache config/routes/views
```

## Architecture

### Request Flow
Routes (`routes/web.php`, `routes/api.php`, `routes/panel/`) → Middleware pipeline (Firewall, Language, CSRF, Turnstile, etc.) → Controllers (`app/Http/Controllers/`) → FormRequest validation → Actions (`app/Actions/`) for business logic → Eloquent Models → Views (Blade + Inertia/Vue).

### Key Architectural Patterns

- **Actions pattern**: Business logic lives in `app/Actions/` (e.g., `UserAction`, `LanguageAction`, `CacheClear`), not in controllers.
- **Domain-organized models**: Models are grouped under `app/Models/Post/`, `app/Models/PersonalNotes/`, `app/Models/Firewall/`, `app/Models/Settings/`, etc.
- **Global settings as cached singletons**: `GlobalVariableServiceProvider` caches settings (general, SEO, ads, languages, theme, social) and shares them across all views.
- **Observer pattern**: `PostsObserver`, `UserObserver`, `ContactMessagesObserver` handle side effects on model events. The `ModelLogger` trait logs model changes.
- **Module system**: Feature modules in `Modules/` (nwidart/laravel-modules) mirror the core app structure. Each has its own routes, views, migrations, tests, and vite.config.js. Module status is toggled in `modules_statuses.json`.

### Admin Panel
The admin panel path is configurable via `ADMIN_PANEL_PATH` env var (default: `/admin`). Panel routes are organized in `routes/panel/` as separate files.

The panel is **Vue 3 + Inertia + Tailwind**, not AdminLTE/Blade. It is a second
Inertia surface, entirely separate from the front-end themes:

| | Panel | Front-end themes |
|---|---|---|
| Root view | `resources/views/panel/app.blade.php` | `resources/views/app.blade.php` |
| Entry | `resources/js/panel/app.js` | `config('theme.assets.*')` |
| Middleware | `HandlePanelInertiaRequests` | `HandleInertiaRequests` |
| Tailwind config | `tailwind.panel.cjs` (via `@config` in `resources/css/panel.css`) | `tailwind.config.js` + theme override |
| Dark mode | `[data-panel-theme="dark"]` | `[data-theme="dark"]` |

Key pieces:

- `app/Support/Panel/PanelResponse::render($component, $bladeView, $props, $viewData)`
  is the **single render point**. Every panel controller goes through it. When
  `PANEL_UI=blade` it falls back to the legacy Blade view, so there is a
  build-free rollback path.
- `config/panel_inertia_routes.php` is the **single migration ledger**. A route
  name listed there is served (and navigated to) via Inertia; anything else is
  still legacy Blade and must be reached with a full page load. Both the server
  (`PanelMenu::isInertia`) and the client (`inertiaRoutes` shared prop) read it.
- `PANEL_UI=vue|blade` plus `PANEL_UI_SCREENS` (CSV allowlist) are the kill switches.
- Panel detection is **path-based** (`config('settings.admin_panel_path')` prefix),
  which covers the core panel and every module panel with one middleware.
- Auth screens (login, OTP, password reset, e-mail verification) live outside the
  panel prefix and are allowlisted by **route name** in `App\Support\Panel\Panel`.

Panel source lives in `resources/js/panel/**` and **is tracked in git** (the
`.gitignore` excludes the rest of `resources/js/`). Front-end themes are not.

### Module panel SDK
Modules publish their panel UI through a fixed contract; the core never names a
module.

| Blade world | Vue equivalent |
|---|---|
| `@extends('panel.base')` | `Modules/<X>/resources/js/panel/Pages/**` + auto-assigned `PanelLayout` |
| `view('<x>::panel.a.b', $d)` | `PanelResponse::render('<x>::A/B', '<x>::panel.a.b', $props, $d)` |
| `@includeIf('<x>::panel.menu')` | `Modules/<X>/config/panel_menu.php` (data, not markup) |
| `@include('panel.partials.x')` | `import X from '~panel/components/X.vue'` |
| `config('dashboard_widgets')` `::` Blade view | `Modules/<X>/resources/js/panel/Widgets/*.vue` |

- `~panel` is a Vite alias for `resources/js/panel`. Modules import core
  components from it — one copy, one CSS bundle, instant core↔module navigation.
- Single build: the root Vite config globs `Modules/*/resources/js/panel/Pages/**`
  and `.../Widgets/*`. A missing module simply yields an empty glob.
- Module panel code is **not** tracked in the core repo (`/Modules` stays in
  `.gitignore`); it is per-site. Only the contract is shared.
- `App\Support\Panel\PanelModuleMenu` resolves a module's menu in three tiers:
  the module's own `config/panel_menu.php`, then `config/panel_menu.local.php`,
  then route-table auto-discovery as a transition fallback.

**Module front-ends are out of scope.** Several modules render Blade views for the
public site (`Modules/<X>/resources/views/{front,layouts,components}/**`,
`resources/assets/**`, `routes/front.php`). Those must not be touched.

### Panel conventions
- Form actions redirect (`back()->with('success', ...)`); data endpoints stay JSON
  and are called with `axios` (search, cascade feeds, editor callbacks, chat turns).
- Endpoints that still feed both worlds are content-negotiated, never rewritten:
  `$request->inertia() ? back()->with(...) : response()->json(...)`.
- Props must be JSON-serialisable: no Eloquent models, no `RouteCollection`, no
  service objects, no pre-rendered HTML. Dates are ISO-8601 and formatted client
  side (the app timezone is passed explicitly as a shared prop).
- Tables are Inertia prop paginators with `only: [...]` partial reloads, not
  DataTables JSON feeds.
- TinyMCE stays self-hosted at `public/themes/panel/js/tinymce`; do not move it to
  npm. Use `~panel/components/TinyMceEditor.vue`.
- A `$request->ajax()` branch also catches Inertia XHR. Guard DataTables feeds with
  `$request->ajax() && ! $request->inertia() && $request->has('draw')`.
- Inertia v2 `<Link prefetch>` fires real GETs on hover. State-changing GET routes
  need a POST alias; the Vue side calls only the POST.

### Authentication Stack
Multi-layered: email/password → optional 2FA (TOTP via Fortify) → optional WebAuthn (FIDO2 via laragear/webauthn). IP-based filtering (blacklist/whitelist) via `FirewallMiddleware`. Cloudflare Turnstile for CAPTCHA on public forms.

### User Roles
`owner` (full access, first user), `admin`, `author`, `editor`, `user`. Authorization via Policy classes and Gates.

### Theme System
Themes can override Vue entry points via env vars (`THEME_ASSET_DIR`, `THEME_CSS_ENTRY`, `THEME_JS_ENTRY`, `THEME_TAILWIND_CONFIG`). Theme-specific `package.json` is auto-installed. The default theme is `resources/js/CryptographVue/`.

### Queue / Async
Mail and notification sending is controlled by `MAIL_SEND_METHOD` and `NOTIFICATION_SEND_METHOD` env vars (`directly` or `queue`). Queue uses database driver. Laravel Horizon monitors workers. Laravel Reverb provides WebSocket support.

### Search
Meilisearch via Laravel Scout. Posts and users are searchable. All search queries are tracked in DB for content idea generation.

## Coding Conventions

- **PHP**: PSR-12, four-space indentation, `StudlyCase` for classes, `camelCase` for methods/variables.
- **Database**: `snake_case` for columns and table names.
- **Components**: PascalCase for Vue/Blade component filenames.
- **Validation**: Use `FormRequest` classes, not inline validation in controllers.
- **Modules**: Mirror module namespaces (`Modules\<Name>\...`) for services, events, tests.

## Key Environment Variables

Beyond standard Laravel config, notable vars include: `CDN_URL`, `ADMIN_PANEL_PATH`, `MEILISEARCH_HOST`/`MEILISEARCH_KEY`, `GEMINI_API_KEY`, `OPENAI_API_KEY`/`OPENAI_MODEL`, `CF_TURNSTILE_SITE_KEY`/`CF_TURNSTILE_SECRET_KEY`, `MAIL_SEND_METHOD`, `NOTIFICATION_SEND_METHOD`, `THEME_ASSET_DIR`, `FONTAWESOME_PRO`.

## Required PHP Extensions

`escapeshellarg`, `escapeshellcmd`, `proc_open`, `proc_get_status`, `proc_close`, `ext-imagick`, `ext-openssl`, `ext-zip`.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/horizon (HORIZON) - v5
- laravel/octane (OCTANE) - v2
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/scout (SCOUT) - v10
- laravel/telescope (TELESCOPE) - v5
- livewire/livewire (LIVEWIRE) - v4
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA) - v2
- vue (VUE) - v3
- laravel-echo (ECHO) - v2
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `inertia-vue-development` — Develops Inertia.js v1 Vue client-side applications. Activates when creating Vue pages, forms, or navigation; using Link or router; or when user mentions Vue with Inertia, Vue pages, Vue forms, or Vue navigation.
- `tailwindcss-development` — Styles applications using Tailwind CSS v3 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

=== inertia-laravel/v2 rules ===

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scrolling (merging props + `WhenVisible`), lazy loading on scroll, polling, prefetching.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 13

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 13 Structure

- Since Laravel 11, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11+ allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit 12 for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

=== laravel/fortify rules ===

## Laravel Fortify

Fortify is a headless authentication backend that provides authentication routes and controllers for Laravel applications.

**Before implementing any authentication features, use the `search-docs` tool to get the latest docs for that specific feature.**

### Configuration & Setup

- Check `config/fortify.php` to see what's enabled. Use `search-docs` for detailed information on specific features.
- Enable features by adding them to the `'features' => []` array: `Features::registration()`, `Features::resetPasswords()`, etc.
- To see the all Fortify registered routes, use the `list-routes` tool with the `only_vendor: true` and `action: "Fortify"` parameters.
- Fortify includes view routes by default (login, register). Set `'views' => false` in the configuration file to disable them if you're handling views yourself.

### Customization

- Views can be customized in `FortifyServiceProvider`'s `boot()` method using `Fortify::loginView()`, `Fortify::registerView()`, etc.
- Customize authentication logic with `Fortify::authenticateUsing()` for custom user retrieval / validation.
- Actions in `app/Actions/Fortify/` handle business logic (user creation, password reset, etc.). They're fully customizable, so you can modify them to change feature behavior.

## Available Features

- `Features::registration()` for user registration.
- `Features::emailVerification()` to verify new user emails.
- `Features::twoFactorAuthentication()` for 2FA with QR codes and recovery codes.
  - Add options: `['confirmPassword' => true, 'confirm' => true]` to require password confirmation and OTP confirmation before enabling 2FA.
- `Features::updateProfileInformation()` to let users update their profile.
- `Features::updatePasswords()` to let users change their passwords.
- `Features::resetPasswords()` for password reset via email.
</laravel-boost-guidelines>


## Orchestration workflow

Use Fable 5 as the lead engineer and orchestrator.

Fable 5 should:

* understand the goal
* create the plan
* split work into clear tasks
* choose the right route for each task
* delegate work when another agent or Codex is a better fit
* review outputs from delegated work
* make the final quality decision

Fable 5 should not do mechanical work unless it is necessary.

Avoid using Fable 5 for:

* broad file scanning
* repetitive file edits
* boilerplate generation
* routine test writing
* formatting-only changes
* running tests without interpretation
* simple refactors with clear acceptance criteria

## Routing rules

Before doing any task, first choose one of these routes:

* Fable direct
* deep-reasoner
* fast-worker
* Codex
* no action

Always explain the routing choice in one sentence.

Use Fable direct for:

* planning
* task decomposition
* final review
* quality decisions
* product or architecture direction
* deciding whether to accept, revise, or escalate

Use deep-reasoner for:

* architecture decisions
* complex debugging
* algorithmic decisions
* reasoning-heavy trade-offs
* risky refactors
* second-opinion analysis before important changes

Use fast-worker for:

* boilerplate
* tests
* formatting
* simple edits
* small refactors
* repetitive mechanical changes
* small documentation updates

Use Codex for:

* well-specified implementation tasks
* codebase investigation
* terminal verification
* UI verification
* test, lint, or build checks
* independent engineering review

If a task clearly matches a subagent or Codex role, prefer delegation instead of doing the work directly.

If you do not delegate, briefly explain why.

Return all important results to Fable 5 before final acceptance.

## Codex execution rule

When the selected route is Codex, do not continue the implementation yourself as Fable 5.

Instead:

1. Create a self-contained Codex brief.
2. Include the task, files or area, constraints, acceptance criteria, and verification command.
3. Use the available Codex command or Codex workflow to delegate the task.
4. Wait for Codex to return the result.
5. Review the Codex result as Fable 5 before accepting it.

Codex brief format:

Task:
[One clear task sentence.]

Files / area:
[Relevant files, folders, components, or system area.]

Constraints:

* Do not touch unrelated files.
* Do not add new dependencies unless explicitly approved.
* Preserve existing behavior outside the requested scope.
* Keep the change as small as safely possible.

Acceptance criteria:

* The requested change is implemented.
* The change is limited to the specified area.
* Existing behavior is preserved.
* No new lint, type, build, or test failures are introduced.

Verification command:
[Insert the relevant command, for example npm test, npm run lint, npm run build, pnpm test, or pnpm lint.]

Expected Codex output:

* Summary of changes
* Files changed
* Verification result
* Risks or follow-up notes

After Codex returns:

* Review the result.
* Decide: accept, revise, or escalate.
* Do not accept Codex output without review.

If Codex is unavailable, say that Codex is unavailable and ask whether to continue directly or use another route.

## Before execution

Before execution:

* produce a short plan
* state the selected route
* state which agent, model, or Codex workflow should handle each part
* ask for confirmation when the task is broad, risky, destructive, or ambiguous

Do not execute broad or risky changes before the user confirms the plan.

## After execution

After execution:

* summarize what changed
* list files changed
* include verification results
* identify remaining risks
* make a clear recommendation: accept, revise, or escalate

## Response format for every task

Start with:

Route:
[Selected route]

Reason:
[One sentence explaining why this route is selected.]

Then continue with the plan, delegation, execution, or review depending on the task.
