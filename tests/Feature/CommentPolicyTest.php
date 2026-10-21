<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentPolicyTest extends TestCase
{
    use RefreshDatabase;

    private CommentPolicy $policy;

    private Comment $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new CommentPolicy;
        $this->comment = Comment::factory()->create();
    }

    public function test_comment_author_can_delete(): void
    {
        $this->assertTrue($this->policy->delete($this->comment->user, $this->comment));
    }

    public function test_post_author_can_delete(): void
    {
        $this->assertTrue($this->policy->delete($this->comment->post->user, $this->comment));
    }

    public function test_admin_can_delete(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($this->policy->delete($admin, $this->comment));
    }

    public function test_unrelated_user_cannot_delete(): void
    {
        $stranger = User::factory()->create();

        $this->assertFalse($this->policy->delete($stranger, $this->comment));
    }
}
