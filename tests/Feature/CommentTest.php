<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_store_comment(): void
    {
        $post = Post::factory()->create();

        $response = $this->post(route('posts.comments.store', $post), ['body' => 'hi']);

        $response->assertRedirect(route('login'));
        $this->assertSame(0, Comment::count());
    }

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.comments.store', $post), ['body' => 'hi']);

        $response->assertRedirect(route('verification.notice'));
        $this->assertSame(0, Comment::count());
    }

    public function test_empty_body_is_rejected(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('posts.comments.store', $post), ['body', '']);

        $response->assertSessionHasErrors(['body']);
        $this->assertSame(0, Comment::count());
    }

    public function test_body_over_1000_char_is_reject(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('posts.comments.store', $post), [
            'body' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors(['body']);
        $this->assertSame(0, Comment::count());
    }

    public function test_user_can_store_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('posts.comments.store', $post), [
            'body' => 'first comment',
        ]);

        $response->assertRedirect(route('posts.show', $post));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'first comment',
            'parent_id' => null,
        ]);
    }

    // ========== 回复 ==========

    public function test_reply_to_top_level_comment_is_stored(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $top = Comment::factory()->create(['post_id' => $post->id]);

        $response = $this->actingAs($user)->post(route('posts.comments.store', $post), [
            'body' => '回复内容',
            'parent_id' => $top->id,
        ]);

        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseHas('comments', [
            'parent_id' => $top->id,
            'post_id' => $post->id, // 回复必须落在与父评论相同的文章下
        ]);
    }

    public function test_reply_cannot_target_another_posts_comment(): void
    {
        // Arrange：目标评论属于另一篇文章
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $otherComment = Comment::factory()->create(); // 嵌套工厂：自带别的文章

        // Act：跨文章挂 parent_id
        $response = $this->actingAs($user)->post(route('posts.comments.store', $post), [
            'body' => '越权回复',
            'parent_id' => $otherComment->id,
        ]);

        // Assert：控制器的归属校验返回 404
        $response->assertNotFound();
        // 目标文章下没有新增评论
        $this->assertDatabaseMissing('comments', ['post_id' => $post->id]);
    }

    public function test_reply_cannot_target_a_reply(): void
    {
        // Arrange：造出"顶层 + 它的回复"
        $post = Post::factory()->create();
        $top = Comment::factory()->create(['post_id' => $post->id]);
        $reply = Comment::factory()->create(['post_id' => $post->id, 'parent_id' => $top->id]);

        // Act：试图把回复挂在"回复"下面（三级嵌套）
        $response = $this->actingAs($post->user)->post(route('posts.comments.store', $post), [
            'body' => '三级嵌套尝试',
            'parent_id' => $reply->id,
        ]);

        // Assert：parent_id 规则的 whereNull 拒绝非顶层目标
        $response->assertSessionHasErrors(['parent_id']);
        // 没有任何新评论在 $reply 下面
        $this->assertDatabaseMissing('comments', ['parent_id' => $reply->id]);
    }

    // ========== 删除 ==========

    public function test_comment_author_can_delete_own_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->actingAs($comment->user)
            ->delete(route('posts.comments.destroy', [$comment->post, $comment]));

        $response->assertRedirect(route('posts.show', $comment->post));
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_post_author_can_delete_comment_on_own_post(): void
    {
        $comment = Comment::factory()->create(); // 路人发的评论
        $author = $comment->post->user;

        $response = $this->actingAs($author)
            ->delete(route('posts.comments.destroy', [$comment->post, $comment]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $comment = Comment::factory()->create();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->delete(route('posts.comments.destroy', [$comment->post, $comment]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_unrelated_user_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create();
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)
            ->delete(route('posts.comments.destroy', [$comment->post, $comment]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]); // 还在
    }

    public function test_deleting_comment_of_another_post_returns_404(): void
    {
        // Arrange：评论属于 A 文；拿一个"本有权限删它"的人去走错误路径（挂在 B 文下）
        $comment = Comment::factory()->create();
        $otherPost = Post::factory()->create();

        $response = $this->actingAs($comment->user)
            ->delete(route('posts.comments.destroy', [$otherPost, $comment]));

        // Assert：归属校验在前、授权在后——URL 拼错返回 404 而非 403
        $response->assertNotFound();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    // ========== 级联删除 ==========

    public function test_deleting_post_cascades_to_comments(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $this->actingAs($post->user)->delete(route('posts.destroy', $post))->assertRedirect();

        $this->assertSame(0, Comment::count());
    }

    public function test_deleting_top_level_comment_cascades_to_replies(): void
    {
        $post = Post::factory()->create();
        $top = Comment::factory()->create(['post_id' => $post->id]);
        Comment::factory()->count(2)->create(['post_id' => $post->id, 'parent_id' => $top->id]);

        $this->actingAs($top->user)
            ->delete(route('posts.comments.destroy', [$post, $top]))
            ->assertRedirect();

        // Assert：顶层没了，挂在它下面的回复也要全部消失（外键 CASCADE）
        $this->assertSame(0, Comment::count());
    }
}
