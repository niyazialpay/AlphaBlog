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
        <div class="card-header d-flex">
            <h3 class="card-title">
                Notes
            </h3>
            <div class="ml-auto">
                <a href="{{route('admin.notes.create')}}"
                   class="btn btn-default"
                   data-bs-toggle="tooltip" data-bs-placement="top"
                   title="@lang('general.new')">
                    <i class="fa-duotone fa-comment-plus"></i>
                    @lang('general.new')
                </a>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">@lang('post.title')</th>
                    <th scope="col" class="text-center" width="200">@lang('general.created_at')</th>
                    <th scope="col" class="text-center" width="200">@lang('general.updated_at')</th>
                    <th scope="col" width="150">@lang('general.actions')</th>
                </thead>
                <tbody>
                @forelse($notes as $note)
                    <tr>
                        <td>
                            <a href="{{route('admin.notes.show', $note)}}">
                                {{$note->title}}
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
                            <a href="javascript:DeleteNote()" class="btn btn-sm btn-danger"
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
        </div>
    </div>
@endsection
