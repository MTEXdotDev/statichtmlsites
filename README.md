# StaticHTMLSites

A Laravel 12 application for hosting static HTML mini-sites.
Each page is accessible via subdomain (`{slug}.statichtmlsites.mtex.dev`) and path URL (`statichtmlsites.mtex.dev/{slug}/...`).

---

## Tech Stack

| Layer    | Technology                       |
|----------|----------------------------------|
| Backend  | Laravel 12, PHP 8.2+             |
| Frontend | Tailwind CSS 3, Alpine.js 3      |
| Editor   | CodeMirror 6 (npm)               |
| Build    | Vite 5 + laravel-vite-plugin     |
| Auth     | Laravel Breeze (blade stack)     |
| Database | SQLite (default) or MySQL        |
| Storage  | Local filesystem                 |

---

## Installation

```bash
# 1. Install PHP + JS dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Migrate
php artisan migrate

# 4. Install Breeze auth scaffolding
php artisan breeze:install blade
php artisan migrate

# 5. Build assets
npm run build
```

Key `.env` variables:

| Variable          | Value                                |
|-------------------|--------------------------------------|
| APP_URL           | https://statichtmlsites.mtex.dev     |
| APP_BASE_DOMAIN   | statichtmlsites.mtex.dev             |
| MAX_UPLOAD_MB     | 50                                   |

---

## Wildcard Subdomain (DNS + Nginx)

**DNS:**
```
*.statichtmlsites.mtex.dev  CNAME  statichtmlsites.mtex.dev
```

**Nginx:**
```nginx
server {
    listen 443 ssl;
    server_name statichtmlsites.mtex.dev *.statichtmlsites.mtex.dev;
    ssl_certificate     /path/to/wildcard.crt;
    ssl_certificate_key /path/to/wildcard.key;
    root /var/www/statichtmlsites/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**config/filesystems.php** — add to the returned array:
```php
'max_upload_mb' => env('MAX_UPLOAD_MB', 50),
```

---

## Project Structure

```
app/
  Http/Controllers/
    DashboardController.php        Dashboard page
    FileManagerController.php      File CRUD JSON API
    PageController.php             Create / update / delete pages
    PageServeController.php        Serve static files + <base> injection
  Models/
    Page.php                       ULID model, storage helpers, slug uniqueness
    User.php                       Breeze user + pages() HasMany
  Policies/PagePolicy.php          Ownership checks
  Providers/AppServiceProvider.php Register FileManagerService singleton + policy
  Services/FileManagerService.php  All file ops: tree, read, save, upload, delete, rename

resources/
  css/app.css                      Tailwind + CodeMirror overrides
  js/
    app.js                         Global entry (Alpine on non-manager pages)
    file-manager.js                CodeMirror 6 + Alpine file-tree component
  views/
    layouts/app.blade.php          App shell with nav
    welcome.blade.php              Marketing landing
    pages/
      dashboard.blade.php          Page grid
      create.blade.php             New page form
      manager.blade.php            Full-screen file manager

database/migrations/
  ..._create_pages_table.php

routes/web.php                     All routes + subdomain group
```

---

## Key Design Decisions

- **`<base>` injection** — `PageServeController` detects subdomain vs. path access and injects `<base href="...">` into every HTML file before serving, keeping relative asset paths correct under both URL shapes.
- **Path traversal guard** — `FileManagerService::guard()` and `PageServeController` both resolve real paths and assert they sit inside the page's storage root.
- **Storage isolation** — `storage/app/pages/{slug}/`. Directories are auto-created on page creation, renamed on slug change, and deleted on page deletion via Eloquent model events.
- **File tree** — Pure PHP recursive directory scan returned as JSON, rendered by an Alpine component with inline HTML strings.

---

## License
MIT
