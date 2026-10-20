<x-layout>
    <x-slot name="title">{{ $post->title }}</x-slot>

    <a href="{{ route('posts.index') }}" class="text-sm text-blue-600 hover:underline">&larr; {{ __('Back to list') }}</a>

    <article class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm mt-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $post->title }}</h1>
        <p class="text-sm text-gray-500 pb-6 mb-6 border-b border-gray-100">
            {{ $post->user->name }} · {{ $post->created_at->format('Y-m-d') }}
        </p>

        <div class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $post->body }}</div>

        @can('update', $post)
            <div class="mt-6 pt-6 border-t border-gray-100 flex items-center gap-6">
                <a href="{{ route('posts.edit', $post) }}"
                   class="leading-8 text-sm font-medium text-blue-600 hover:underline">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('posts.destroy', $post) }}"
                      onsubmit="return confirmSubmit(this)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex h-8 items-center text-sm font-medium text-red-600 hover:text-red-800">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        @endcan
    </article>

    {{-- 评论表单：登录且已验证才可发表（与发文同门槛）；访客给登录引导 --}}
    <section class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm mt-6">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900">
            {{ __('Comments') }}
            {{-- 计数徽章：数据来自 loadCount，不加额外查询 --}}
            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">{{ $post->comments_count }}</span>
        </h2>

        @auth
            <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mt-4 flex gap-3">
                {{-- 当前用户头像（首字母），与评论列表视觉呼应 --}}
                <div class="h-9 w-9 shrink-0 rounded-full bg-blue-600 text-white text-sm font-semibold flex items-center justify-center select-none">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 space-y-3">
                    <textarea name="body" rows="3" required maxlength="1000"
                              placeholder="{{ __('Write a comment') }}"
                              class="block w-full resize-none rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none transition">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="flex justify-end">
                        <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                            {{ __('Post') }}
                        </button>
                    </div>
                </div>
            </form>
        @else
            <p class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-500">
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:underline">{{ __('Please log in to comment.') }}</a>
            </p>
        @endauth

        @if ($comments->isEmpty())
            {{-- 空状态：居中 + 图形占位，避免"页面缺了东西"的观感 --}}
            <div class="mt-8 mb-2 flex flex-col items-center text-center">
                <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM21 12c0 4.556-4.03 8.25-9 8.25a9.76 9.76 0 0 1-2.555-.337A5.97 5.97 0 0 1 5.41 20.97a5.97 5.97 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>
                </svg>
                <p class="mt-3 text-sm text-gray-400">{{ __('No comments yet.') }}</p>
            </div>
        @else
            <ul class="mt-6 divide-y divide-gray-100">
                @foreach ($comments as $comment)
                    {{-- 头像底色按用户 id 从固定色板取值：同一人颜色稳定，无需存库 --}}
                    @php
                        $avatarColors = ['bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-violet-500', 'bg-rose-500', 'bg-cyan-500'];
                        $avatar = $avatarColors[$comment->user->id % count($avatarColors)];
                    @endphp
                    <li class="flex gap-3 py-5 first:pt-0 last:pb-0">
                        <div class="h-9 w-9 shrink-0 rounded-full {{ $avatar }} text-white text-sm font-semibold flex items-center justify-center select-none">
                            {{ mb_strtoupper(mb_substr($comment->user->name, 0, 1)) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            {{-- 名字 + 作者徽章 + 时间 --}}
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                @if ($comment->user_id === $post->user_id)
                                    <span class="rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-600">{{ __('Author') }}</span>
                                @endif
                                <span class="text-xs text-gray-400">{{ $comment->created_at->format('Y-m-d H:i') }}</span>
                            </div>

                            <p class="text-sm text-gray-700 mt-1 whitespace-pre-line break-words">{{ $comment->body }}</p>

                            {{-- items-start 顶对齐：回复表单展开撑高此行时，删除按钮钉在行首不动 --}}
                            <div class="mt-2 flex items-start gap-4">
                                @can('delete', $comment)
                                    <form method="POST" action="{{ route('posts.comments.destroy', [$post, $comment]) }}"
                                          onsubmit="return confirmSubmit(this)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-red-600 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                            </svg>
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                @endcan

                                @auth
                                    {{-- details 折叠实现"点击展开内联回复表单"，无需 JS；flex-1 让展开后的表单占满整行 --}}
                                    <details class="group/reply flex-1 min-w-0">
                                        <summary class="inline-flex cursor-pointer select-none items-center gap-1 text-xs text-gray-400 hover:text-blue-600 transition list-none [&::-webkit-details-marker]:hidden">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                                            </svg>
                                            {{ __('Reply') }}
                                        </summary>
                                        <form method="POST" action="{{ route('posts.comments.store', $post) }}"
                                              class="mt-3 space-y-3">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <textarea name="body" rows="3" required maxlength="1000"
                                                      placeholder="{{ __('Reply to :name', ['name' => $comment->user->name]) }}"
                                                      class="block w-full resize-none rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none transition"></textarea>
                                            <div class="flex justify-end">
                                                <button type="submit"
                                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                                                    {{ __('Post') }}
                                                </button>
                                            </div>
                                        </form>
                                    </details>
                                @endauth
                            </div>

                            {{-- 回复平铺在父评论下（设计上只有一层，无递归） --}}
                            @if ($comment->replies->isNotEmpty())
                                <ul class="mt-4 space-y-4 border-l-2 border-gray-100 pl-4">
                                    @foreach ($comment->replies as $reply)
                                        @php
                                            $replyAvatar = $avatarColors[$reply->user->id % count($avatarColors)];
                                        @endphp
                                        <li class="flex gap-2.5">
                                            <div class="h-7 w-7 shrink-0 rounded-full {{ $replyAvatar }} text-white text-xs font-semibold flex items-center justify-center select-none">
                                                {{ mb_strtoupper(mb_substr($reply->user->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-medium text-gray-900">{{ $reply->user->name }}</span>
                                                    @if ($reply->user_id === $post->user_id)
                                                        <span class="rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-600">{{ __('Author') }}</span>
                                                    @endif
                                                    <span class="text-xs text-gray-400">{{ $reply->created_at->format('Y-m-d H:i') }}</span>
                                                </div>
                                                <p class="text-sm text-gray-700 mt-1 whitespace-pre-line break-words">{{ $reply->body }}</p>

                                                @can('delete', $reply)
                                                    <form method="POST" action="{{ route('posts.comments.destroy', [$post, $reply]) }}"
                                                          onsubmit="return confirmSubmit(this)"
                                                          class="mt-1">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-red-600 transition">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                                            </svg>
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            {{ $comments->links() }}
        @endif
    </section>
</x-layout>
