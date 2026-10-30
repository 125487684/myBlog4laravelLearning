<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 可见性规则收敛
        $posts = Post::query()
            ->visibleTo(Auth::user())
            ->with('user')
            ->latest()
            ->orderByDesc('id')
            ->paginate(10);

        return view('posts.index', ['posts' => $posts]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $post = Post::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('posts.show', $post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        // 隐藏文章对作者、管理员之外返回404；
        if ($post->hidden_at && ! Post::visibleTo(Auth::user())->whereKey($post->getKey())->exists()) {
            abort(404);
        }

        $post->loadCount('comments');

        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with('user', 'replies.user')
            ->latest()
            ->paginate(10);

        return view('posts.show', [
            'post' => $post,
            'comments' => $comments,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('posts.edit', ['post' => $post]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return redirect()->route('posts.show', $post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index');
    }

    /**
     * 切换文章隐藏状态
     */
    public function toggleHidden(Post $post)
    {
        $this->authorize('hide', $post);

        $post->hidden_at = $post->hidden_at ? null : now();
        $post->save();

        return redirect()->route('posts.show', $post)
            ->with('status', $post->hidden_at ? __('Post hidden.') : __('Post unhidden.'));
    }

    /**
     * 我发布的
     */
    public function mine()
    {
        $posts = Auth::user()->posts()
            ->latest()
            ->orderByDesc('id')
            ->paginate(10);

        return view('posts.mine', ['posts' => $posts]);
    }

    /**
     * 我参与的
     */
    public function participated()
    {
        $user = Auth::user();

        $posts = Post::query()
            ->visibleTo($user)
            ->where(function (Builder $q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('comments', fn ($c) => $c->where('user_id', $user->id));
            })
            ->with('user')
            ->latest()
            ->orderByDesc('id')
            ->paginate(10);

        return view('posts.participated', ['posts' => $posts]);
    }
}
