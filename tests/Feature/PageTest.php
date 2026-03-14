<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('pages');
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function test_dashboard_requires_auth(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_user_pages(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        $mine   = Page::factory()->for($user)->create(['name' => 'My Page']);
        $theirs = Page::factory()->for($other)->create(['name' => 'Their Page']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Page')
            ->assertDontSee('Their Page');
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_create_page_form_renders(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('pages.create'))
            ->assertOk();
    }

    public function test_user_can_create_page_with_auto_slug(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('pages.store'), ['name' => 'My Awesome Site'])
            ->assertRedirect();

        $this->assertDatabaseHas('pages', [
            'user_id' => $user->id,
            'name'    => 'My Awesome Site',
            'slug'    => 'my-awesome-site',
        ]);
    }

    public function test_user_can_create_page_with_custom_slug(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('pages.store'), ['name' => 'Test', 'slug' => 'custom-slug'])
            ->assertRedirect();

        $this->assertDatabaseHas('pages', ['slug' => 'custom-slug']);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        $user = User::factory()->create();
        Page::factory()->for($user)->create(['slug' => 'taken']);

        $this->actingAs($user)
            ->post(route('pages.store'), ['name' => 'Another', 'slug' => 'taken'])
            ->assertSessionHasErrors('slug');
    }

    public function test_slug_with_invalid_chars_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('pages.store'), ['name' => 'Test', 'slug' => 'invalid slug!'])
            ->assertSessionHasErrors('slug');
    }

    // ── Update settings ───────────────────────────────────────────────────────

    public function test_owner_can_update_page_settings(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->for($user)->create(['name' => 'Old', 'slug' => 'old-slug']);

        $this->actingAs($user)
            ->put(route('pages.settings', 'old-slug'), [
                'name'      => 'New Name',
                'slug'      => 'new-slug',
                'is_public' => true,
            ])
            ->assertRedirect(route('pages.manager', 'new-slug'));

        $this->assertDatabaseHas('pages', ['name' => 'New Name', 'slug' => 'new-slug']);
    }

    public function test_other_user_cannot_update_settings(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $page  = Page::factory()->for($owner)->create(['slug' => 'my-page']);

        $this->actingAs($other)
            ->put(route('pages.settings', 'my-page'), [
                'name' => 'Hacked', 'slug' => 'my-page', 'is_public' => true,
            ])
            ->assertNotFound();
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_owner_can_delete_page(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->for($user)->create(['slug' => 'to-delete']);

        $this->actingAs($user)
            ->delete(route('pages.destroy', 'to-delete'))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('pages', ['slug' => 'to-delete']);
    }

    public function test_other_user_cannot_delete_page(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        Page::factory()->for($owner)->create(['slug' => 'my-page']);

        $this->actingAs($other)
            ->delete(route('pages.destroy', 'my-page'))
            ->assertNotFound();

        $this->assertDatabaseHas('pages', ['slug' => 'my-page']);
    }
}
