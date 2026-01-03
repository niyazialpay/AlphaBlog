<a href="{{route('admin.post.edit', [$type, $post])}}">
    {{stripslashes($post->title)}} @if(!$post->is_published)
        <em>(@lang('post.draft'))</em> @endif
</a>
