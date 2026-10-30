<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PostPolicy $policy;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new PostPolicy;
        // 基准场景：路人甲的文章
        $this->post = Post::factory()->create();
    }

    public function test_author_can_hide(): void
    {
        $this->assertTrue($this->policy->hide($this->post->user, $this->post));
    }

    public function test_stranger_cannot_hide(): void
    {
        $stranger = User::factory()->create();

        $this->assertFalse($this->policy->hide($stranger, $this->post));
    }

    public function test_admin_cannot_hide_others_post(): void
    {
        // 设计决策：管理员"能看"隐藏文章，但没有"替作者隐藏/恢复"的权力
        $admin = User::factory()->admin()->create();

        $this->assertFalse($this->policy->hide($admin, $this->post));
    }
}
