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
- Webauthn Login
- 2-Factor Authentication
- AI Chatbot - Based by Gemini
- IP Filter (Blacklist and Whitelist)
- User management
- Cloudflare Turnstile (similar to Google Recaptcha)
- Email SMTP
- Media Library
- MySQL
- Laravel 11
- PHP 8.3
- Bootstrap 5
- Font Awesome 6.5.1 (Pro)

