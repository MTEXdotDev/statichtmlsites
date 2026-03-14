# Static HTML Sites

A Laravel 12 application that lets authenticated users create and serve static mini-sites.  
Each page is accessible via **subdomain** (`{slug}.statichtmlsites.mtex.dev`) and **path** (`statichtmlsites.mtex.dev/{slug}/`).

---

## Stack

| Layer      | Choice                         |
|------------|--------------------------------|
| Backend    | Laravel 12                     |
| Frontend   | Livewire v3 + Alpine.js        |
| Editor     | CodeMirror 6 (via ESM CDN)     |
| Auth       | Laravel Breeze                 |
| DB         | MySQL / SQLite                 |
| Storage    | Local disk (`storage/app/pages/`) |

---

## Installation

```bash
# 1. Clone & install
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Set APP_BASE_DOMAIN in .env
#    APP_BASE_DOMAIN=statichtmlsites.mtex.dev

# 4. Database
php artisan migrate

# 5. Install Breeze (if not committed)
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build

# 6. Livewire
composer require livewire/livewire

# 7. Storage
php artisan storage:link
mkdir -p storage/app/pages

# 8. Serve
php artisan serve
```

---

## Subdomain Routing (production)

Add a wildcard DNS record:

```
*.statichtmlsites.mtex.dev  →  your server IP
```

Nginx vhost:

```nginx
server {
    listen 443 ssl;
    server_name statichtmlsites.mtex.dev *.statichtmlsites.mtex.dev;

    root /var/www/statichtmlsites/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
}
```

---

## File Structure (generated files)

```
app/
  Http/Controllers/
    PageController.php          ← CRUD for pages
    PageServeController.php     ← serves static files + base-tag injection
  Livewire/
    FileManager.php             ← full IDE Livewire component
  Models/
    Page.php
    User.php
  Policies/
    PagePolicy.php
  Providers/
    AppServiceProvider.php

database/migrations/
  ..._create_pages_table.php

resources/views/
  layouts/app.blade.php
  pages/
    index.blade.php             ← dashboard
    create.blade.php
  livewire/
    file-manager.blade.php      ← full IDE UI with CodeMirror 6

routes/
  web.php                       ← includes subdomain + path serving routes

config/
  app.php                       ← adds base_domain, max_upload_mb
  filesystems.php               ← adds 'pages' disk
```

---

## Key Design Decisions

### Base-tag injection
`PageServeController` injects `<base href="…">` into every HTML response so relative
asset references (`./style.css`, `src="video.mp4"`) resolve correctly regardless of
whether the page is accessed via subdomain or path.

### File storage
Files are stored directly on disk (not in the database) under `storage/app/pages/{slug}/`.
The Livewire `FileManager` reads/writes the filesystem via Laravel's `Storage::disk('pages')`.

### Path traversal prevention
All file paths are validated to reject `..` segments and leading `/` before any disk operation.

### MIME detection
`PageServeController` uses a manual extension→MIME map (no `finfo` dependency) and streams
binary files directly without loading them into memory via `response()->file()`.
