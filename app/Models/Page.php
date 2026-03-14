<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Absolute path to this page's storage root.
     */
    public function storagePath(string $relative = ''): string
    {
        $base = storage_path("app/pages/{$this->slug}");
        return $relative ? $base . '/' . ltrim($relative, '/') : $base;
    }

    /**
     * Laravel disk-relative path (for Storage facade calls).
     */
    public function diskPath(string $relative = ''): string
    {
        $base = "pages/{$this->slug}";
        return $relative ? $base . '/' . ltrim($relative, '/') : $base;
    }

    /**
     * Public URL for subdomain access.
     */
    public function subdomainUrl(string $path = ''): string
    {
        $base = "https://{$this->slug}." . config('app.base_domain', 'statichtmlsites.mtex.dev');
        return $path ? $base . '/' . ltrim($path, '/') : $base . '/';
    }

    /**
     * Public URL for path-based access.
     */
    public function pathUrl(string $path = ''): string
    {
        $base = config('app.url') . '/' . $this->slug;
        return $path ? $base . '/' . ltrim($path, '/') : $base . '/';
    }

    /**
     * Generate a unique slug from a name.
     */
    public static function makeUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    // ── Boot ─────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Ensure storage directory exists when a page is created
        static::created(function (Page $page) {
            Storage::makeDirectory($page->diskPath());

            // Bootstrap with a blank index.html
            if (! Storage::exists($page->diskPath('index.html'))) {
                Storage::put($page->diskPath('index.html'), static::defaultHtml($page->name));
            }
        });

        // Remove files when a page is deleted
        static::deleted(function (Page $page) {
            Storage::deleteDirectory($page->diskPath());
        });

        // If slug changes, rename the storage directory
        static::updating(function (Page $page) {
            if ($page->isDirty('slug')) {
                $old = $page->getOriginal('slug');
                $new = $page->slug;
                Storage::move("pages/{$old}", "pages/{$new}");
            }
        });
    }

    private static function defaultHtml(string $name): string
    {
        $escaped = htmlspecialchars($name, ENT_QUOTES);
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$escaped}</title>
            <style>
                body { font-family: system-ui, sans-serif; display: flex; align-items: center;
                       justify-content: center; min-height: 100vh; margin: 0; background: #f8fafc; }
                h1   { color: #1e293b; }
            </style>
        </head>
        <body>
            <h1>Welcome to {$escaped}</h1>
        </body>
        </html>
        HTML;
    }
}
