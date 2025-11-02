# Repository Guidelines

## Project Structure & Module Organization
- Core Laravel code sits in `app/` with environment-wide config in `config/` and shared routes in `routes/`.
- Feature modules (`Modules/Podcast`, `Modules/XSayfaMuhasebe`) mirror the nwidart layout (`app/`, `resources/`, `routes/`, `tests/`) and are toggled through `modules_statuses.json`.
- Shared views and assets belong in `resources/` (`views/`, `js/app.js`, `lang/`), compiled files deploy from `public/`, and `database/migrations` or `seeders` control schema updates alongside Meilisearch index settings.

## Build, Test, and Development Commands
- Bootstrap dependencies with `composer install` and `npm install`; repeat `npm install` inside a module when its `package.json` changes.
- Use `php artisan serve` for the backend, `npm run dev` for shared assets, and `npm run dev` from a module directory to watch module bundles.
- Keep data aligned via `php artisan migrate --seed` and confirm module state with `php artisan module:list`.
- Quality checks: `./vendor/bin/pint` formats PHP, `npm run lint` runs ESLint, and `php artisan scout:sync-index-settings` refreshes search mappings after schema changes.

## Coding Style & Naming Conventions
- Follow PSR-12 with four-space indentation (`.editorconfig`, `.styleci.yml`); controllers, jobs, and listeners use `StudlyCase` suffixes within the proper namespace.
- Use camelCase in PHP, snake_case for database columns, and PascalCase for React or Blade component filenames.
- Prefer `FormRequest` classes for validation and mirror module namespaces (`Modules\<Name>\...`) when adding services, events, or tests.

## Testing Guidelines
- Place HTTP and integration coverage in `tests/Feature/*Test.php`; isolated logic goes in `tests/Unit`.
- Module-specific tests live in `Modules/<Name>/tests`; run targeted suites with `php artisan test --filter=Podcast` or run everything via `php artisan test`.
- Cover queue jobs, events, and Meilisearch interactions when modifying those touchpoints.

## Commit & Pull Request Guidelines
- Write small, imperative commits (`Fix authors view fallback`) and reference issues with `Fixes #123` where relevant.
- Before submitting a PR, run linting and tests, summarise the change, list manual checks, and attach UI screenshots when Blade or Inertia output changes.
- Note any new environment keys (Meilisearch, Gemini, OpenAI, Cloudflare Turnstile) or PHP extension requirements so reviewers can reproduce.

## Security & Configuration Tips
- Never commit `.env`; update `.env.example` instead and remind deployers about required PHP functions (`proc_*`, `escapeshell*`).
- Queue behaviour respects `MAIL_SEND_METHOD` and `NOTIFICATION_SEND_METHOD`; call out changes that affect worker throughput or Horizon tuning.
- For new external integrations, add config defaults under `config/` and update the README installation checklist for operators.
