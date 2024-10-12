<tr>
    <td class="p-1">
        @if($level > 0)
            <i class="fa-sharp-duotone fa-solid fa-arrow-turn-down-right"></i>
        @endif
        @if($categories->getFirstMediaUrl('categories', 'thumb'))
            <img src="{{ $categories->getFirstMediaUrl('categories', 'thumb') }}" alt="{{ $categories->name }}"
                 class="img-fluid" width="50">
        @endif
    </td>
    <td class="p-1">
        @if($level > 0)
            <i class="fa-sharp-duotone fa-solid fa-arrow-turn-down-right"></i>
        @endif
        {{ $categories->name }}
    </td>
    <td>
        <a href="{{ route('admin.categories', $categories) }}"
           class="btn btn-sm btn-primary mx-1"
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="@lang('general.edit')">
            <i class="fa fa-edit"></i>
        </a>
        <a href="javascript:Delete('{{ $categories->id }}')"
           class="btn btn-sm btn-danger mx-1"
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="@lang('general.delete')">
            <i class="fa fa-trash"></i>
        </a>
    </td>
</tr>

@forelse($categories->children as $child)
    @include('panel.post.category.partials.category_row', ['categories' => $child, 'level' => $level + 1])
@empty
@endforelse
