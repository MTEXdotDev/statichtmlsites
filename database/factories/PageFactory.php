<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'user_id'   => User::factory(),
            'name'      => ucwords($name),
            'slug'      => Str::slug($name),
            'is_public' => true,
        ];
    }

    public function private(): static
    {
        return $this->state(['is_public' => false]);
    }

    public function withSlug(string $slug): static
    {
        return $this->state(['slug' => $slug]);
    }
}
