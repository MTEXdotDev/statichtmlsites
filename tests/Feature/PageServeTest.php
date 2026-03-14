<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageServeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('pages');
    }

    // ── Path-based serving ────────────────────────────────────────────────────

    public function test_public_page_index_is_served_via_path(): void
    {
        $page = Page::factory()->create(['slug' => 'hello', 'is_public' => true]);
        Storage::disk('pages')->put('hello/index.html', '<html><head></head><body>Hi</body></html>');

        $this->get('/hello')
            ->assertOk()
            ->assertSee('Hi');
    }

    public function test_base_tag_is_injected_into_html(): void
    {
        $page = Page::factory()->create(['slug' => 'demo', 'is_public' => true]);
        Storage::disk('pages')->put('demo/index.html', '<html><head><title>Test</title></head><body></body></html>');

        $response = $this->get('/demo');
        $content  = $response->content();

        $this->assertStringContainsString('<base href=', $content);
    }

    public function test_base_tag_appears_as_first_child_of_head(): void
    {
        $page = Page::factory()->create(['slug' => 'test', 'is_public' => true]);
        Storage::disk('pages')->put('test/index.html', '<html><head><title>X</title></head><body></body></html>');

        $html = $this->get('/test')->content();
        // <base> must come right after <head>
        $this->assertMatchesRegularExpression('/<head[^>]*>\s*<base href=/', $html);
    }

    public function test_css_file_is_served_with_correct_mime(): void
    {
        Page::factory()->create(['slug' => 'assets', 'is_public' => true]);
        Storage::disk('pages')->put('assets/style.css', 'body { color: red; }');

        $this->get('/assets/style.css')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/css');
    }

    public function test_nested_file_is_served(): void
    {
        Page::factory()->create(['slug' => 'nested', 'is_public' => true]);
        Storage::disk('pages')->put('nested/js/app.js', 'console.log("hi")');

        $this->get('/nested/js/app.js')
            ->assertOk()
            ->assertSee('console.log');
    }

    public function test_directory_path_resolves_to_index_html(): void
    {
        Page::factory()->create(['slug' => 'sub', 'is_public' => true]);
        Storage::disk('pages')->put('sub/about/index.html', '<html><head></head><body>About</body></html>');

        $this->get('/sub/about')
            ->assertOk()
            ->assertSee('About');
    }

    public function test_missing_file_returns_404(): void
    {
        Page::factory()->create(['slug' => 'exists', 'is_public' => true]);

        $this->get('/exists/nope.html')->assertNotFound();
    }

    // ── Private pages ─────────────────────────────────────────────────────────

    public function test_private_page_returns_403_for_guests(): void
    {
        $page = Page::factory()->create(['slug' => 'secret', 'is_public' => false]);
        Storage::disk('pages')->put('secret/index.html', '<html><head></head><body>Secret</body></html>');

        $this->get('/secret')->assertForbidden();
    }

    public function test_private_page_is_accessible_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        Page::factory()->for($user)->create(['slug' => 'private', 'is_public' => false]);
        Storage::disk('pages')->put('private/index.html', '<html><head></head><body>Private</body></html>');

        $this->actingAs($user)
            ->get('/private')
            ->assertOk();
    }

    // ── Path traversal protection ─────────────────────────────────────────────

    public function test_path_traversal_is_blocked(): void
    {
        Page::factory()->create(['slug' => 'pwned', 'is_public' => true]);

        // Try to escape the page directory via encoded traversal
        $this->get('/pwned/../../../etc/passwd')->assertNotFound();
    }

    // ── HTML-less documents ───────────────────────────────────────────────────

    public function test_base_tag_prepended_when_no_head_tag(): void
    {
        Page::factory()->create(['slug' => 'nohead', 'is_public' => true]);
        Storage::disk('pages')->put('nohead/index.html', '<p>Minimal</p>');

        $html = $this->get('/nohead')->content();
        $this->assertStringStartsWith('<base href=', ltrim($html));
    }
}
