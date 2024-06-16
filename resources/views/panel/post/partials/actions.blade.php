<a href="{{route('admin.post.edit', [$type, $post])}}"
   class="btn btn-sm btn-primary mx-1 my-2"
   data-bs-toggle="tooltip" data-bs-placement="top"
   title="@lang('general.edit')">
    <i class="fa fa-edit"></i>
</a>
<a href="javascript:DeleteBlog('{{$post->id}}')"
   class="btn btn-sm btn-danger mx-1 my-2"
   data-bs-toggle="tooltip" data-bs-placement="top"
   title="@lang('general.delete')">
    <i class="fa fa-trash"></i>
</a>
