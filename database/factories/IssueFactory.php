<?php

namespace Database\Factories;

use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2020, 2026);

        return [
            'title' => 'Proceedings of ICSMI '.$year,
            'slug' => 'icsmi-'.$year.'-'.Str::lower(Str::random(6)),
            'year' => $year,
            'published_at' => now()->subDay(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }
}
