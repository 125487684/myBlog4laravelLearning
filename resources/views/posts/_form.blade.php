<label> title
    <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}">
</label><br>
<label> slug
    <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}">
</label><br>
<label> text
    <textarea name="body" cols="60" rows="10">{{ old('body', $post->body ?? '') }}</textarea>
</label><br>
<button type="submit">{{ $buttonText ?? 'submit' }}</button>