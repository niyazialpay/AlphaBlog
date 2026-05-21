@php $comments = $widgetData['comments'] ?? collect(); @endphp
<div class="card h-100 mb-0 border-0 d-flex flex-column">
    <div class="card-header py-2 px-3 border-0 bg-transparent d-flex justify-content-between align-items-center">
        <small class="text-muted font-weight-bold">Son Yorumlar</small>
        <a href="{{ route('admin.post.comments') }}" class="btn btn-xs btn-outline-secondary" style="font-size:10px">Tümü →</a>
    </div>
    <div class="card-body p-0 flex-grow-1 overflow-auto">
        @if($comments->isNotEmpty())
        <table class="table table-sm table-borderless mb-0">
            <tbody>
                @foreach($comments as $comment)
                <tr>
                    <td style="font-size:11px">
                        <div class="font-weight-bold">{{ $comment->user?->name ?? 'Anonim' }}</div>
                        <div class="text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ Str::limit($comment->comment ?? '', 60) }}</div>
                    </td>
                    <td class="text-muted text-right" style="font-size:10px;white-space:nowrap">{{ $comment->created_at?->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-muted small p-3">Henüz yorum yok</div>
        @endif
    </div>
</div>
