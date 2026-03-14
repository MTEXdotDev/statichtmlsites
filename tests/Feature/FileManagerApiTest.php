<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileManagerApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('pages');

        $this->user = User::factory()->create();
        $this->page = Page::factory()->for($this->user)->create(['slug' => 'testsite']);
        Storage::disk('pages')->put('testsite/index.html', '<html><head></head><body></body></html>');
    }

    // ── File listing ──────────────────────────────────────────────────────────

    public function test_can_list_files(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('pages.files.list', 'testsite'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'index.html']);
    }

    public function test_list_requires_ownership(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other)
            ->getJson(route('pages.files.list', 'testsite'))
            ->assertNotFound();
    }

    // ── Read ──────────────────────────────────────────────────────────────────

    public function test_can_read_file_content(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('pages.files.read', 'testsite') . '?path=index.html')
            ->assertOk()
            ->assertJsonPath('path', 'index.html')
            ->assertJsonStructure(['content', 'path']);
    }

    public function test_read_nonexistent_file_returns_error(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('pages.files.read', 'testsite') . '?path=ghost.html')
            ->assertStatus(422);
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function test_can_save_file_content(): void
    {
        $this->actingAs($this->user)
            ->putJson(route('pages.files.save', 'testsite'), [
                'path'    => 'index.html',
                'content' => '<html><head></head><body>Updated</body></html>',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertStringContainsString(
            'Updated',
            Storage::disk('pages')->get('testsite/index.html')
        );
    }

    public function test_save_rejects_path_traversal(): void
    {
        $this->actingAs($this->user)
            ->putJson(route('pages.files.save', 'testsite'), [
                'path'    => '../other-site/index.html',
                'content' => 'evil',
            ])
            ->assertStatus(422);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_can_create_new_file(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('pages.files.create', 'testsite'), ['path' => 'about.html'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertTrue(Storage::disk('pages')->exists('testsite/about.html'));
    }

    public function test_creating_duplicate_file_returns_error(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('pages.files.create', 'testsite'), ['path' => 'index.html'])
            ->assertStatus(422);
    }

    // ── Upload ────────────────────────────────────────────────────────────────

    public function test_can_upload_image(): void
    {
        $file = UploadedFile::fake()->image('logo.png');

        $this->actingAs($this->user)
            ->post(route('pages.files.upload', 'testsite'), ['file' => $file])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertTrue(Storage::disk('pages')->exists('testsite/logo.png'));
    }

    public function test_upload_rejects_disallowed_extension(): void
    {
        $file = UploadedFile::fake()->create('shell.php', 10, 'text/plain');

        $this->actingAs($this->user)
            ->post(route('pages.files.upload', 'testsite'), ['file' => $file])
            ->assertStatus(422);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_can_delete_file(): void
    {
        Storage::disk('pages')->put('testsite/old.css', 'p{}');

        $this->actingAs($this->user)
            ->deleteJson(route('pages.files.delete', 'testsite'), ['path' => 'old.css'])
            ->assertOk();

        $this->assertFalse(Storage::disk('pages')->exists('testsite/old.css'));
    }

    public function test_can_delete_folder(): void
    {
        Storage::disk('pages')->makeDirectory('testsite/old-dir');
        Storage::disk('pages')->put('testsite/old-dir/file.txt', 'x');

        $this->actingAs($this->user)
            ->deleteJson(route('pages.files.delete', 'testsite'), ['path' => 'old-dir'])
            ->assertOk();

        $this->assertFalse(Storage::disk('pages')->directoryExists('testsite/old-dir'));
    }

    // ── Folder ────────────────────────────────────────────────────────────────

    public function test_can_create_folder(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('pages.files.folder', 'testsite'), ['path' => 'assets'])
            ->assertOk();

        $this->assertTrue(Storage::disk('pages')->directoryExists('testsite/assets'));
    }

    // ── Rename ────────────────────────────────────────────────────────────────

    public function test_can_rename_file(): void
    {
        Storage::disk('pages')->put('testsite/old.txt', 'content');

        $this->actingAs($this->user)
            ->putJson(route('pages.files.rename', 'testsite'), [
                'from' => 'old.txt',
                'to'   => 'new.txt',
            ])
            ->assertOk();

        $this->assertFalse(Storage::disk('pages')->exists('testsite/old.txt'));
        $this->assertTrue(Storage::disk('pages')->exists('testsite/new.txt'));
    }
}
