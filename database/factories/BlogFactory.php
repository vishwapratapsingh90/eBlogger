<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            "slug" => $this->faker->slug(),
            "title" => $this->faker->sentence(),
            "content" => $this->faker->paragraphs(3, true),
            "author_id" => \App\Models\User::factory(),
        ];
    }
}
