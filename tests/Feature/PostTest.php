<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_loggedUserCreateArticle(): void
    {
        // Arrange：造一个用户
        $user = User::factory()->create();
        $slug = 'test-post';

        // Act：用该用户创建
        $response = $this->actingAs($user)->post('/posts', [
            'title' => '测试文章',
            'slug' => $slug,
            'body' => 'forTest',
        ]);

        // Assert：断言
        $response->assertRedirect(route('posts.show', $slug));
        $this->assertDatabaseHas('posts', [
            'slug' => $slug,
            'user_id' => $user->id,
        ]);
        $this->assertEquals(1, Post::count());
    }

    public function test_userEditOtherArticle(): void
    {
        $author = User::factory()->create();
        $attacker = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        $response = $this->actingAs($attacker)->put("/posts/{$post->slug}", [
            'title' => 'hacked',
            'slug' => 'hacked',
            'body' => 'hacked',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('posts', ['slug' => 'hacked']);
    }

    public function test_unloggedUserRedirectToLogin(): void
    {
        $response = $this->get('/posts/create');

        $response->assertRedirect(route('login'));
    }

    public function test_failToEmptySubmit(): void
    {
        // Arrange
        $author = User::factory()->create();

        // Act
        $response = $this->actingAs($author)->post('/posts', [
            'title' => '',
            'slug' => '',
            'body' => '',
        ]);

        // Assert
        $response->assertSessionHasErrors(['title', 'slug', 'body']);
    }

    public function test_creatSameArticle(): void
    {
        // Arrange
        $author = User::factory()->create();
        $this->actingAs($author)->post('/posts', [
            'title' => 'for test same article',
            'slug' => 'same',
            'body' => 'for test same article',
        ]);

        // Act
        $response = $this->actingAs($author)->post('/posts', [
            'title' => 'for test same article',
            'slug' => 'same',
            'body' => 'for test same article',
        ]);

        // Assert
        $response->assertSessionHasErrors(['slug']);
        $this->assertEquals(1, Post::count());
    }

    public function test_slugWithBlank(): void
    {
        // Arrange
        $author = User::factory()->create();

        // Act
        $response = $this->actingAs($author)->post('/posts', [
            'title' => 'title',
            'slug' => 'slug with blank',
            'body' => 'body',
        ]);

        // Assert
        $response->assertSessionHasErrors(['slug']);
        $this->assertEquals(0, Post::count());
    }

    public function test_pagination(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 11; $i++) {
            $n = sprintf('%02d', $i);
            Post::factory()->for($user)->create([
                'title' => "post-{$n}",
                'slug' => "post-{$n}",
            ]);
        }

        $this->get('/posts')->assertSee('post-11')->assertDontSee('post-01');
        $this->get('/posts?page=2')->assertSee('post-01')->assertDontSee('post-11');
    }
}
