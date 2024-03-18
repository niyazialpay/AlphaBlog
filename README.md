## About This Project
# <a href="https://alphablog.dev>" target="_blank">Alpha Blog</a>

MongoDB is used as the database in this blog system prepared with Laravel. The in-site search engine is provided by Meilisearch.

In order for Laravel to work with MongoDB, the official "mongodb/laravel-mongodb" package prepared by MongoDB was added to this project. In addition, Meilisearch integration was made compatible with MongoDB for the system to work correctly.

Adding images to blog or page content and sizing them was done with Spatie Media Library, but this package was not compatible with MongoDB. To make it compatible, I forked the spatie/laravel-medialibrary repo to my own github account and included it in this project with composer after making the necessary edits.

#### For this system to work, the following PHP functions must not be disabled on the server:

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

## Features

- Blogs with Categories
- Page
- Personal notes
  - Your personal notes are stored encrypted in the database.
  - Your encryption key is not stored in the database. If you forget your encryption key, you will not be able to access your notes. 
  - Your notes can not accessed by the admin or any other user.
- In-site search engine (Meilisearch)
- Admin panel
- IP Filter (Blacklist and Whitelist)
- User management
- Cloudflare Turnstile (similar to Google Recaptcha)
- Email SMTP
- Media Library
- MongoDB
- Laravel 11
- PHP 8.3
- Bootstrap 5
- Font Awesome 6.5.1 (Pro)

