@props(['post'])

{{-- 文章卡片：index / mine / participated 共用；隐藏徽章只对"能看见这篇隐藏文章的人"渲染（列表查询已过滤） --}}
<article {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow']) }}>
    <h2 class="text-xl font-semibold mb-2">
        <a href="{{ route('posts.show', $post) }}"
            class="text-gray-900 hover:text-blue-600">{{ $post->title }}</a>
        @if ($post->hidden_at)
            <span class="ml-2 inline-block align-middle rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">{{ __('Hidden') }}</span>
        @endif
    </h2>
    <p class="text-sm text-gray-500 mb-3">
        {{ $post->user->name }} · {{ $post->created_at->format('Y-m-d') }}
    </p>
    <p class="text-gray-600 leading-relaxed">{{ Str::limit($post->body, 100) }}</p>
</article>
