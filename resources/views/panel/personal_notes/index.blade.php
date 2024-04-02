@extends('panel.base')
@section('title', __('notes.notes'))

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            @lang('notes.notes')
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex">
                <h3 class="card-title">
                    Notes
                </h3>
                <div class="ml-auto">
                    <a href="{{route('admin.notes.create')}}"
                       class="btn btn-outline-primary"
                       data-bs-toggle="tooltip" data-bs-placement="top"
                       title="@lang('general.new')">
                        <i class="fa-duotone fa-comment-plus"></i>
                        @lang('general.new')
                    </a>
                </div>
            </div>
            <p class="d-block bg-warning text-center my-2 p-2 rounded text-danger">
                @lang('notes.encrypted_note')
            </p>
        </div>
        <div class="card-body table-responsive">
            <div class="col-12 mb-3">
                <label for="category_id">@lang('notes.category')</label>
                <select name="category_id" id="category_id" class="form-control"
                        onchange="window.location='{{route('admin.notes')}}?category='+this.value">
                    <option value="">@lang('notes.select_category')</option>
                    @foreach($categories as $category)
                        <option value="{{$category->id}}"
                                @if(request()->get('category') == $category->id) selected @endif>
                            {{$category->name}}
                        </option>
                    @endforeach
                </select>
            </div>
            <form method="post" id="searchForm" action="javascript:void(0)">
                <div class="input-group mb-3">
                    <input class="form-control form-control-navbar" type="search" name="search" placeholder="@lang('general.search')" aria-label="@lang('general.search')" value="{{GetPost(request()->search)}}">
                    <div class="input-group-append">
                        <button class="btn btn-navbar search-button" type="submit">
                            <i class="fa-duotone fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </form>
            <table class="table table-striped" aria-describedby="notes">
                <thead>
                <tr>
                    <th scope="col">@lang('post.title')</th>
                    <th scope="col">@lang('notes.category')</th>
                    <th scope="col" class="text-center" style="width: 200px">@lang('general.created_at')</th>
                    <th scope="col" class="text-center" style="width: 200px">@lang('general.updated_at')</th>
                    <th scope="col" style="width: 150px">@lang('general.actions')</th>
                </thead>
                <tbody>
                @forelse($notes as $note)
                    <tr>
                        <td>
                            <a href="{{route('admin.notes.show', $note)}}">
                                {{$note->title}}
                            </a>
                        </td>
                        <td>
                            <a href="{{route('admin.notes')}}?category={{$note->category->id}}">
                                {{$note->category->name}}
                            </a>
                        </td>
                        <td class="text-center">
                            {{$note->created_at}}
                        </td>
                        <td class="text-center">
                            {{$note->updated_at}}
                        </td>
                        <td>
                            <a href="{{route('admin.notes.media', $note)}}" class="btn btn-sm btn-primary"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               data-bs-title="@lang('post.media')">
                                <i class="fa-solid fa-images"></i>
                            </a>
                            <a href="{{route('admin.notes.edit', $note)}}" class="btn btn-sm btn-primary"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               data-bs-title="@lang('general.edit')">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="javascript:DeleteNote('{{$note->id}}')" class="btn btn-sm btn-danger"
                               data-bs-toggle="tooltip" data-bs-placement="top"
                               data-bs-title="@lang('general.delete')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            @lang('notes.not_found')
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{$notes->withQueryString()->links()}}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function DeleteNote(note_id) {
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: "@lang('general.you_wont_be_able_to_revert_this')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '@lang('general.delete_confirm_yes')',
                cancelButtonText: '@lang('general.cancel')',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.notes')}}/delete/' + note_id,
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function (response) {
                            Swal.fire({
                                title: '@lang('general.deleted')',
                                text: '@lang('notes.deleted')',
                                icon: 'success',
                                confirmButtonText: '@lang('general.ok')',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            })
                        }
                    })
                }
            })
        }
        $(document).ready(function () {
            $('#searchForm').submit(function () {
                let search = $('input[name=search]').val();
                location.href = '{{route('admin.notes')}}?search=' + search;
            })
        });
    </script>
@endsection
