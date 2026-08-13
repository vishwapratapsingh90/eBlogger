<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $blogs = [
            [
                'slug' => 'first-blog',
                'title' => 'First Blog',
                'content' => 'This is the content of the first blog.',
                'author_id' => User::findOrFail(1)->id,
            ],
            [
                'slug' => 'second-blog',
                'title' => 'Second Blog',
                'content' => 'This is the content of the second blog.',
                'author_id' => User::findOrFail(2)->id,
            ],
        ];

        foreach ($blogs as $blog) {
            if (Blog::where('slug', $blog['slug'])->exists()) {
                continue;
            }

            try {
                Blog::factory()->create($blog);
            } catch (\Illuminate\Database\QueryException  $e) {
                // Ignore duplicate or constraint errors and continue seeding.
                continue;
            }
        }
    }
}
