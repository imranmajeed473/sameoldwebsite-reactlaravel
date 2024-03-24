<?php

namespace Tests\Feature\Roles;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Feature\Traits\DisablesVite;
use Tests\Feature\Traits\WithRoles;
use Tests\TestCase;

class ArticlesAccessTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;
    use WithFaker;
    use DisablesVite;

    /**
     * Tests user is authorized to get articles.
     *
     * @return void
     */
    public function testCanGetArticles(): void
    {
        $response = $this->withRoles(['write_posts'])->getJson('/api/blog/articles');

        $response->assertSuccessful();
    }

    /**
     * Tests user is unauthorized to get articles.
     *
     * @return void
     */
    public function testCannotGetArticles(): void
    {
        $response = $this->withRoles([])->getJson('/api/blog/articles');

        $response->assertForbidden();
    }

    /**
     * Tests user is authorized to get article.
     *
     * @return void
     */
    public function testCanGetArticle(): void
    {
        $article = Article::factory()->create();

        $response = $this->withRoles(['write_posts'])->getJson(sprintf('/api/blog/articles/%d', $article->getKey()));

        $response->assertSuccessful();
    }

    /**
     * Tests user is unauthorized to get article.
     *
     * @return void
     */
    public function testCannotGetArticle(): void
    {
        $article = Article::factory()->create();

        $response = $this->withRoles([])->getJson(sprintf('/api/blog/articles/%d', $article->getKey()));

        $response->assertForbidden();
    }

    /**
     * Tests user is authorized to create articles.
     *
     * @return void
     */
    public function testCanCreateArticle(): void
    {
        $title = Str::headline($this->faker->unique()->realText(25));

        $response = $this->withRoles(['write_posts'])->postJson('/api/blog/articles', [
            'title' => $title,
            'slug' => Str::slug($title),
            'revision' => [
                'content' => $this->faker()->paragraphs(4, true),
            ],
        ]);

        $response->assertSuccessful();
    }

    /**
     * Tests user is unauthorized to create articles.
     *
     * @return void
     */
    public function testCannotCreateArticle(): void
    {
        $response = $this->withRoles([])->postJson('/api/blog/articles', [
            'icon' => $this->faker->iconName(),
            'skill' => $this->faker->unique()->jobTitle(),
        ]);

        $response->assertForbidden();
    }

    /**
     * Tests user is authorized to update articles.
     *
     * @return void
     */
    public function testCanUpdateArticle(): void
    {
        $article = Article::factory()->create();

        $response = $this->withRoles(['write_posts'])->putJson(sprintf('/api/blog/articles/%d', $article->getKey()), [
            'icon' => $this->faker->iconName(),
            'skill' => $this->faker->unique()->jobTitle(),
        ]);

        $response->assertSuccessful();
    }

    /**
     * Tests user is unauthorized to update articles.
     *
     * @return void
     */
    public function testCannotUpdateArticle(): void
    {
        $article = Article::factory()->create();

        $response = $this->withRoles([])->putJson(sprintf('/api/blog/articles/%d', $article->getKey()), [
            'skill' => $this->faker->unique()->jobTitle(),
        ]);

        $response->assertForbidden();
    }

    /**
     * Tests user is authorized to delete articles.
     *
     * @return void
     */
    public function testCanDeleteArticle(): void
    {
        $article = Article::factory()->create();

        $response = $this->withRoles(['write_posts'])->deleteJson(sprintf('/api/blog/articles/%d', $article->getKey()));

        $response->assertSuccessful();
    }

    /**
     * Tests user is unauthorized to delete articles.
     *
     * @return void
     */
    public function testCannotDeleteArticle(): void
    {
        $article = Article::factory()->create();

        $response = $this->withRoles([])->deleteJson(sprintf('/api/blog/articles/%d', $article->getKey()));

        $response->assertForbidden();
    }

    /**
     * Tests user is authorized to restore articles.
     *
     * @return void
     */
    public function testCanRestoreArticle(): void
    {
        $article = Article::factory()->deleted()->create();

        $response = $this->withRoles(['write_posts'])->postJson(sprintf('/api/blog/articles/restore/%d', $article->getKey()));

        $response->assertSuccessful();
    }

    /**
     * Tests user is unauthorized to restore articles.
     *
     * @return void
     */
    public function testCannotRestoreArticle(): void
    {
        $article = Article::factory()->deleted()->create();

        $response = $this->withRoles([])->postJson(sprintf('/api/blog/articles/restore/%d', $article->getKey()));

        $response->assertForbidden();
    }
}
