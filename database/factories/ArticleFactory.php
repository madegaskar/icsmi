<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'issue_id' => Issue::factory(),
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'title' => $title,
            'abstract' => fake()->paragraph(),
            'published_at' => now()->subDay(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }
}
