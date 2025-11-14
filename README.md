## About This Project
# <a href="https://alphablog.dev>" target="_blank">Alpha Blog</a>

I had previously designed the database of this system using MongoDB, but due to various compatibility issues, I had to redesign it using MySQL.

The in-site search engine is provided by Meilisearch.


#### The following PHP functions need to be enabled on the server for this system to work:

```bash
escapeshellarg, escapeshellcmd, proc_open, proc_get_status, proc_close 
```

### For installation after git clone process

```bash
composer update --no-dev
```

```bash
cp .env.example .env
```

Edit the .env file for database information, Meilisearch, Email SMTP and Cloudflare Turnstile (similar to Google Recaptcha) definitions

```bash
php artisan key:generate
```

```bash
php artisan storage:link
```

```bash
php artisan migrate
```

## MeiliSearch Integration
You need to use Meilisearch for the in-site search engine. After registering at https://www.meilisearch.com/, you can get the master key and add it to your .env file with the address given to you. If you do not want to create an account on the Meilisearch website, you can use it by installing it on your own server. You can get information about this from this [link](https://niyazi.net/en/meilisearch-and-laravel-integration).


```bash
MEILISEARCH_HOST=
MEILISEARCH_KEY=
```

```bash
php artisan scout:sync-index-settings
```

#### After all this, if you want, you can change the admin panel path from the ADMIN_PANEL_PATH variable in the .env file

```bash
php artisan optimize
```

To create a user after these operations

```bash
php artisan app:create-user
```

To delete posts and comments in the trash, as well as words you don't intend to use in search words

```bash
php artisan app:clear-trash
```

Cron job definition to automatically delete unused search words, trashed posts and comments from the last 30 days

```bash
* * * * * php artisan schedule:run >> /dev/null 2>&1
```

or
```bash
0 0 * * *  php artisan clear-trash >> /dev/null 2>&1
```

### For activate Gemini Chatbot
If you want to activate Gemini Chatbot you have to get an API key from https://makersuite.google.com/app/apikey and add it to the .env file

```bash
GEMINI_API_KEY=
```

### For activate ChatGPT Chatbot
If you want to activate ChatGPT Chatbot you have to get an API key from https://platform.openai.com/api-keys and add it to the .env file

```bash
OPENAI_API_KEY=
```

Also you can change ChatGPT model from the .env file

```bash
OPENAI_MODEL=
```

## Cloudflare Turnstile Integration
By creating an account on Cloudflare, you can get your site key and secret key for Turnstile and add it to your .env file.

```bash
CF_TURNSTILE_SITE_KEY=
CF_TURNSTILE_SECRET_KEY=
```

### Note
By default, notification and contact emails are sent directly. If you change this to queue in the .env file, these sends will be queued and happen in the background

```bash
MAIL_SEND_METHOD=queue
NOTIFICATION_SEND_METHOD=queue
```



## Features
- Blogs with Categories
- Page
- Personal notes
  - Your personal notes are stored encrypted in the database.
  - Your encryption key is not stored in the database. If you forget your encryption key, you will not be able to access your notes. 
  - Your notes can not accessed by the admin or any other user.
- In-site search engine (Meilisearch)
  - The words searched in the search engine are kept in the database
    - It saves the words that are not found among the words in the on-site searches to generate ideas for content on that topic later.
- Admin panel
- OneSignal Push Notification for Admin
  - New Comment Notification
  - Searched Word Notification
- Webauthn Login
- 2-Factor Authentication
- AI Chatbot - Based by Gemini or ChatGPT
- IP Filter (Blacklist and Whitelist)
- User management
- Cloudflare Turnstile (similar to Google Recaptcha)
- Email SMTP
- Media Library
- MySQL
- Laravel 12
- PHP 8.3
- Bootstrap 5
- Font Awesome 6.5.1

## Theme-Specific Asset Overrides
Each deployment can keep its Vue theme (and its npm dependencies) outside of git. Point `THEME_ASSET_DIR` to that gitignored folder (for example `resources/js/CryptographVue`) and the application will automatically treat `<dir>/app.css` and `<dir>/app.js` as the default Vite inputs, expose the directory through `config('theme.paths.asset_dir')`, and install the theme’s `package.json` before every `npm run dev/build/preview`. If your theme uses different filenames, override them explicitly:

```bash
THEME_ASSET_DIR=resources/js/CryptographVue
THEME_CSS_ENTRY=
THEME_JS_ENTRY=
THEME_TAILWIND_CONFIG=resources/js/CryptographVue/tailwind.config.js
THEME_PACKAGE_DIR=
```

`THEME_CSS_ENTRY` / `THEME_JS_ENTRY` fall back to `<THEME_ASSET_DIR>/app.css|app.js` when left empty, while `THEME_PACKAGE_DIR` defaults to the same directory. Tailwind configs can export either a full config object or a function that receives the base config and returns the final one; both CommonJS (`module.exports = ...`) and ES modules (`export default ...`) are supported.

When the resolved package directory (from `THEME_PACKAGE_DIR`, `THEME_ASSET_DIR` or the entry files) contains a `package.json`, the npm scripts automatically run `npm install` there **only when needed** (missing `node_modules` or a newer `package-lock.json`). You can force a reinstall manually via:

```bash
npm run theme:install
```

This keeps shared dependencies such as Pusher in the root `package.json` while allowing each site to own its bespoke theme, package.json and node_modules without committing them to git or affecting other deployments.
