<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes storage directories that no longer have a matching Page row.
 * Scheduled daily via routes/console.php.
 *
 * Usage:  php artisan pages:cleanup-storage [--dry-run]
 */
class CleanupPageStorage extends Command
{
    protected $signature   = 'pages:cleanup-storage {--dry-run : List orphaned directories without deleting}';
    protected $description = 'Remove storage directories for deleted pages.';

    public function handle(): int
    {
        $disk = Storage::disk('pages');

        // Top-level directories on the pages disk are page slugs
        $dirs = $disk->directories('/');

        $slugs   = Page::pluck('slug')->flip(); // O(1) lookups
        $orphans = collect($dirs)->filter(fn ($d) => ! $slugs->has($d));

        if ($orphans->isEmpty()) {
            $this->info('No orphaned directories found.');
            return self::SUCCESS;
        }

        $dryRun = $this->option('dry-run');

        foreach ($orphans as $dir) {
            if ($dryRun) {
                $this->line("  [dry-run] Would delete: {$dir}");
            } else {
                $disk->deleteDirectory($dir);
                $this->line("  Deleted: {$dir}");
            }
        }

        $count = $orphans->count();
        $verb  = $dryRun ? 'Found' : 'Cleaned up';
        $this->info("{$verb} {$count} orphaned " . str('directory')->plural($count) . '.');

        return self::SUCCESS;
    }
}
