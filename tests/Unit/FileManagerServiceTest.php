<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Services\FileManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class FileManagerServiceTest extends TestCase
{
    use RefreshDatabase;

    private FileManagerService $service;
    private Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('pages');

        $this->service = new FileManagerService();
        $this->page    = Page::factory()->create(['slug' => 'mysite']);
        Storage::disk('pages')->put('mysite/index.html', '<h1>Hello</h1>');
    }

    public function test_tree_returns_files(): void
    {
        $tree = $this->service->tree($this->page);
        $names = array_column($tree, 'name');
        $this->assertContains('index.html', $names);
    }

    public function test_tree_orders_dirs_before_files(): void
    {
        Storage::disk('pages')->makeDirectory('mysite/assets');
        Storage::disk('pages')->put('mysite/readme.txt', 'hi');

        $tree  = $this->service->tree($this->page);
        $types = array_column($tree, 'type');

        // Directories should precede files
        $firstFile = array_search('file', $types);
        $firstDir  = array_search('directory', $types);
        $this->assertLessThan($firstFile, $firstDir);
    }

    public function test_can_read_file(): void
    {
        $content = $this->service->read($this->page, 'index.html');
        $this->assertStringContainsString('Hello', $content);
    }

    public function test_read_missing_file_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->service->read($this->page, 'ghost.html');
    }

    public function test_can_save_file(): void
    {
        $this->service->save($this->page, 'index.html', '<h1>Updated</h1>');
        $this->assertStringContainsString('Updated', Storage::disk('pages')->get('mysite/index.html'));
    }

    public function test_can_create_file(): void
    {
        $this->service->createFile($this->page, 'new.html');
        $this->assertTrue(Storage::disk('pages')->exists('mysite/new.html'));
    }

    public function test_create_duplicate_file_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->service->createFile($this->page, 'index.html');
    }

    public function test_can_delete_file(): void
    {
        $this->service->delete($this->page, 'index.html');
        $this->assertFalse(Storage::disk('pages')->exists('mysite/index.html'));
    }

    public function test_can_delete_directory(): void
    {
        Storage::disk('pages')->makeDirectory('mysite/subdir');
        Storage::disk('pages')->put('mysite/subdir/file.txt', 'x');

        $this->service->delete($this->page, 'subdir');
        $this->assertFalse(Storage::disk('pages')->directoryExists('mysite/subdir'));
    }

    public function test_can_create_folder(): void
    {
        $this->service->createFolder($this->page, 'media');
        $this->assertTrue(Storage::disk('pages')->directoryExists('mysite/media'));
    }

    public function test_can_rename_file(): void
    {
        $this->service->rename($this->page, 'index.html', 'home.html');
        $this->assertFalse(Storage::disk('pages')->exists('mysite/index.html'));
        $this->assertTrue(Storage::disk('pages')->exists('mysite/home.html'));
    }

    public function test_path_traversal_is_blocked(): void
    {
        $this->expectException(RuntimeException::class);
        $this->service->read($this->page, '../othersite/secret.txt');
    }

    public function test_upload_allowed_file(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');
        $this->service->upload($this->page, $file);
        $this->assertTrue(Storage::disk('pages')->exists('mysite/photo.jpg'));
    }

    public function test_upload_disallowed_extension_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $file = UploadedFile::fake()->create('evil.exe', 10);
        $this->service->upload($this->page, $file);
    }
}
