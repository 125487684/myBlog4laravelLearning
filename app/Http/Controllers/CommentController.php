<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * 保存新创建的文章评论
     */
    public function store(StoreCommentRequest $request, Post $post)
    {
        $data = $request->validated();

        // 归属校验
        if (isset($data['parent_id'])) {
            $parent = Comment::findOrFail($data['parent_id']);
            abort_unless($parent->post_id === $post->id, 404);
        }

        // 显式赋值id
        $comment = new Comment($data);
        $comment->post_id = $post->id;
        $comment->user_id = Auth::id();
        $comment->save();

        return redirect()->route('posts.show', $post)
            ->with('status', __('Comment posted.'));
    }

    /**
     * 删除特定评论
     */
    public function destory(Post $post, Comment $comment)
    {
        abort_unless($comment->post_id === $post->id, 404);

        // 评论者，文章作者，管理员可操作 (CommentPolicy@delete)
        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()->route('posts.show', $post)
            ->with('status', __('Comment deleted.'));
    }
}
