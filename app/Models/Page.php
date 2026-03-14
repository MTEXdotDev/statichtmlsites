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

    protected $fillable = ['user_id', 'name', 'slug', 'is_public'];

    protected $casts = ['is_public' => 'boolean'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Storage path helpers ──────────────────────────────────────────────────

    /**
     * Path relative to the "pages" disk root (storage/app/pages/).
     * Used with Storage::disk('pages').
     *   e.g.  my-site/index.html
     */
    public function storagePath(string $relative = ''): string
    {
        return $relative
            ? $this->slug . '/' . ltrim($relative, '/')
            : $this->slug;
    }

    /**
     * Path relative to the default "local" disk root (storage/app/).
     * Used with Storage::disk('local') / the Storage facade without a disk.
     *   e.g.  pages/my-site/index.html
     */
    public function diskPath(string $relative = ''): string
    {
        $base = 'pages/' . $this->slug;
        return $relative ? $base . '/' . ltrim($relative, '/') : $base;
    }

    /**
     * Ensure the storage directory exists.
     */
    public function ensureStorageExists(): void
    {
        Storage::disk('pages')->makeDirectory($this->slug);
    }

    // ── URL helpers ───────────────────────────────────────────────────────────

    public function subdomainUrl(string $path = ''): string
    {
        $base = 'https://' . $this->slug . '.' . config('app.base_domain', 'statichtmlsites.mtex.dev');
        return $path ? rtrim($base, '/') . '/' . ltrim($path, '/') : $base . '/';
    }

    public function pathUrl(string $path = ''): string
    {
        $base = rtrim(config('app.url'), '/') . '/' . $this->slug;
        return $path ? $base . '/' . ltrim($path, '/') : $base . '/';
    }

    // ── Slug factory ──────────────────────────────────────────────────────────

    public static function makeUniqueSlug(string $name): string
    {
        $slug     = Str::slug($name);
        $original = $slug;
        $counter  = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    // ── Model events ──────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::created(function (Page $page) {
            Storage::disk('pages')->makeDirectory($page->slug);

            if (! Storage::disk('pages')->exists($page->storagePath('index.html'))) {
                Storage::disk('pages')->put(
                    $page->storagePath('index.html'),
                    static::defaultHtml($page->name)
                );
            }
        });

        static::deleted(function (Page $page) {
            Storage::disk('pages')->deleteDirectory($page->slug);
        });

        static::updating(function (Page $page) {
            if ($page->isDirty('slug')) {
                $old = $page->getOriginal('slug');
                $new = $page->slug;
                Storage::disk('pages')->move($old, $new);
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
