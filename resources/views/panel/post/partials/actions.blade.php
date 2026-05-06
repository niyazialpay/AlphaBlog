<a href="{{route('admin.post.edit', [$type, $post])}}"
   class="btn btn-sm btn-primary mx-1 my-2"
   data-bs-toggle="tooltip" data-bs-placement="top"
   title="@lang('general.edit')">
    <i class="fa fa-edit"></i>
</a>
@if($post->post_type == 'post')
    @if($post->qr_link)
        <button class="btn btn-sm btn-info mx-1 my-2 qr-show-btn"
                data-qr-url="{{ $post->qr_link }}"
                data-scan-count="{{ $post->qr_scans_count ?? 0 }}"
                data-bs-toggle="tooltip" data-bs-placement="top"
                title="QR Kodu Göster ({{ $post->qr_scans_count ?? 0 }} okuma)">
            <i class="fas fa-qrcode"></i>
            @if(($post->qr_scans_count ?? 0) > 0)
                <span class="badge badge-light">{{ $post->qr_scans_count }}</span>
            @endif
        </button>
    @else
        <button class="btn btn-sm btn-secondary mx-1 my-2 qr-generate-btn"
                data-post-id="{{ $post->id }}"
                data-bs-toggle="tooltip" data-bs-placement="top"
                title="QR Oluştur">
            <i class="fas fa-qrcode"></i>
        </button>
    @endif
@endif
<a href="javascript:DeleteBlog('{{$post->id}}')"
   class="btn btn-sm btn-danger mx-1 my-2"
   data-bs-toggle="tooltip" data-bs-placement="top"
   title="@lang('general.delete')">
    <i class="fa fa-trash"></i>
</a>
