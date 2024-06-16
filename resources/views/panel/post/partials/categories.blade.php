@foreach($post->categories as $category)
    <span class="badge badge-primary">
                                                    <a href="{{route('admin.post.category', [
                                                            $type,
                                                            $category->id,
                                                            'language' => request()->get('language')
                                                        ])}}"
                                                       class="text-white">
                                                        {{stripslashes($category->name)}}
                                                    </a>
                                                </span>
@endforeach
