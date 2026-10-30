<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_my_pages(): void
    {
        // 两个"我的"页面都要求登录
        $this->get(route('posts.mine'))->assertRedirect(route('login'));
        $this->get(route('posts.participated'))->assertRedirect(route('login'));
    }

    public function test_my_posts_lists_only_own_posts(): void
    {
        $mine = User::factory()->create();
        $other = User::factory()->create();

        Post::factory()->for($mine)->create(['title' => 'mine-1']);
        Post::factory()->for($mine)->create(['title' => 'mine-2']);
        Post::factory()->for($other)->create(['title' => 'other-1']);

        $response = $this->actingAs($mine)->get(route('posts.mine'));

        $response->assertSee('mine-1')
            ->assertSee('mine-2')
            ->assertDontSee('other-1');
    }

    public function test_my_posts_includes_hidden_posts(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['title' => 'hidden-own']);
        // hidden_at 不在 $fillable 白名单中，不能走 update() 批量赋值，需逐属性赋值
        $post->hidden_at = now();
        $post->save();

        // "我的"是所有权查询：隐藏的也在列表里（视图带徽章）
        $this->actingAs($user)->get(route('posts.mine'))->assertSee('hidden-own');
    }

    public function test_participated_includes_commented_posts_of_others(): void
    {
        $author = User::factory()->create();
        $commenter = User::factory()->create();

        $post = Post::factory()->for($author)->create(['title' => 'commented-on']);
        Comment::factory()->create(['post_id' => $post->id, 'user_id' => $commenter->id]);

        // 我评论过别人的文章 → 出现在"我的参与"（先断言 200，防止错误页假阳性）
        $this->actingAs($commenter)
            ->get(route('posts.participated'))
            ->assertOk()
            ->assertSee('commented-on');
    }
}
