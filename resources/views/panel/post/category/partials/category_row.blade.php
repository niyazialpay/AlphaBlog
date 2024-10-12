<tr>
    <td class="p-1">
        @if($level > 0)
            <i class="fa-sharp-duotone fa-solid fa-arrow-turn-down-right"></i>
        @endif
        @if($category->getFirstMediaUrl('categories', 'thumb'))
            <img src="{{ $category->getFirstMediaUrl('categories', 'thumb') }}" alt="{{ $category->name }}"
                 class="img-fluid" width="50">
        @endif
    </td>
    <td class="p-1">
        @if($level > 0)
            <i class="fa-sharp-duotone fa-solid fa-arrow-turn-down-right"></i>
        @endif
        {{ $category->name }}
    </td>
    <td>
        <a href="{{ route('admin.categories', $category) }}"
           class="btn btn-sm btn-primary mx-1"
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="@lang('general.edit')">
            <i class="fa fa-edit"></i>
        </a>
        <a href="javascript:Delete('{{ $category->id }}')"
           class="btn btn-sm btn-danger mx-1"
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="@lang('general.delete')">
            <i class="fa fa-trash"></i>
        </a>
    </td>
</tr>

@forelse($category->children as $child)
    @include('panel.post.category.partials.category_row', ['category' => $child, 'level' => $level + 1])
@empty
@endforelse
