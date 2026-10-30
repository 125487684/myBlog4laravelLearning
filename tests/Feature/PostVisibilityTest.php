<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_see_hidden_post_in_list(): void
    {
        // Arrange：造一篇并隐藏
        $post = Post::factory()->create();
        $post->hidden_at = now();
        $post->save();

        // Act/Assert：访客列表看不到它
        $this->get('/posts')->assertDontSee($post->title);
    }

    public function test_author_sees_own_hidden_post_in_list(): void
    {
        $post = Post::factory()->create();
        $post->hidden_at = now();
        $post->save();

        // 作者自己的列表仍可见（visibleTo 的 orWhere 分支）
        $this->actingAs($post->user)->get('/posts')->assertSee($post->title);
    }

    public function test_admin_sees_hidden_posts_in_list(): void
    {
        $post = Post::factory()->create();
        $post->hidden_at = now();
        $post->save();

        // 管理员全量可见
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/posts')->assertSee($post->title);
    }

    public function test_guest_gets_404_on_hidden_post_detail(): void
    {
        $post = Post::factory()->create();
        $post->hidden_at = now();
        $post->save();

        // 直链访问：404 而非 403——不向无关者暴露"文章存在"
        $this->get(route('posts.show', $post))->assertNotFound();
    }

    public function test_author_can_view_own_hidden_post_detail(): void
    {
        $post = Post::factory()->create();
        $post->hidden_at = now();
        $post->save();

        $this->actingAs($post->user)->get(route('posts.show', $post))->assertOk();
    }

    public function test_stranger_cannot_toggle_hidden(): void
    {
        $post = Post::factory()->create();
        $other = User::factory()->create();

        // 非作者调用切换接口：hide 策略拒绝
        $this->actingAs($other)
            ->patch(route('posts.hidden.toggle', $post))
            ->assertStatus(403);

        // 数据库未受影响
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'hidden_at' => null]);
    }

    public function test_guest_cannot_toggle_hidden(): void
    {
        $post = Post::factory()->create();

        $this->patch(route('posts.hidden.toggle', $post))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'hidden_at' => null]);
    }

    public function test_toggle_unhide_restores_public_visibility(): void
    {
        $post = Post::factory()->create();
        $post->hidden_at = now();
        $post->save();

        // Act：作者取消隐藏
        $this->actingAs($post->user)
            ->patch(route('posts.hidden.toggle', $post))
            ->assertRedirect(route('posts.show', $post))
            ->assertSessionHas('status');

        // Assert：hidden_at 归 NULL，访客重新可见
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'hidden_at' => null]);
        $this->get('/posts')->assertSee($post->title);
    }

    public function test_participated_list_does_not_leak_others_hidden_posts(): void
    {
        // 本用例锁最危险的洞：orWhereHas 击穿 visibleTo
        // Arrange：A 发文并隐藏；B 评论过这篇文章
        $post = Post::factory()->create();
        $post->hidden_at = now();
        $post->save();

        $commenter = User::factory()->create();
        Comment::factory()->create(['post_id' => $post->id, 'user_id' => $commenter->id]);

        // Act：B 打开"我的参与"
        $response = $this->actingAs($commenter)->get(route('posts.participated'));

        // Assert：B 确实评论过它，但它已隐藏——必须不可见
        // （若 where 闭包缺失，orWhereHas 会绕过 visibleTo 把它捞出来）
        $response->assertDontSee($post->title);
    }
}
