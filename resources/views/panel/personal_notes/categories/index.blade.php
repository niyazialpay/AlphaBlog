@extends('panel.base')
@section('title',__('categories.categories'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.notes')}}">
                @lang('notes.notes')
            </a>
        </li>
        <li class="breadcrumb-item active">
            @lang('categories.categories')
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <p class="d-block bg-warning text-center my-2 p-2 rounded text-danger">
                @lang('notes.encrypted_note')
            </p>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6  table-responsive border-end-1">
                    <div class="tab-content">
                        <table class="table table-striped" aria-describedby="@lang('categories.categories')">
                            <thead>
                            <tr>
                                <th scope="@lang('categories.name')">
                                    @lang('categories.name')
                                </th>
                                <th scope="@lang('general.actions')" style="width: 200px;">
                                    @lang('general.actions')
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($categories as $item)
                                <tr>

                                    <td>
                                        {{$item->name}} ({{$item->notes->count()}})
                                    </td>
                                    <td>
                                        <a href="{{route('admin.notes.category', $item)}}"
                                           class="btn btn-sm btn-primary mx-1"
                                           data-bs-toggle="tooltip" data-bs-placement="top"
                                           title="@lang('general.edit')">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="javascript:Delete('{{$item->id}}')"
                                           class="btn btn-sm btn-danger mx-1"
                                           data-bs-toggle="tooltip" data-bs-placement="top"
                                           title="@lang('general.delete')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">
                                        @lang('categories.error_not_found')
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <th scope="@lang('categories.name')">
                                    @lang('categories.name')
                                </th>
                                <th scope="@lang('general.actions')">
                                    @lang('general.actions')
                                </th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="col-6">
                    <a href="{{route('admin.notes.categories')}}"
                       data-bs-toggle="tooltip" data-bs-placement="top"
                       title="@lang('general.new')"
                       class="btn btn-sm btn-primary">
                        <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-plus"></i> @lang('general.new')
                    </a>
                    <form class="row" method="post" id="category_create_form" action="javascript:void(0);">
                        <div class="col-12 mb-3">
                            <label for="name">@lang('categories.name')</label>
                            <input type="text" class="form-control" name="name" id="name"
                                   placeholder="@lang('categories.name_placeholder')" value="{{$category->name}}">
                        </div>
                        @csrf
                        <input type="hidden" name="id" value="{{$category->id}}">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">@if($category->id)
                                    @lang('general.update')
                                @else
                                    @lang('general.create')
                                @endif </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function Delete(id) {
            //delete confirmation
            Swal.fire({
                title: "@lang('general.are_you_sure')",
                text: "@lang('general.you_wont_be_able_to_revert_this')",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "@lang('general.delete_confirm_yes')",
                cancelButtonText: "@lang('general.delete_confirm_no')",
                reverseButtons: true
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: "{{route('admin.notes.categories.delete')}}" + "/" + id,
                        type: "POST",
                        data: {
                            _token: "{{csrf_token()}}"
                        },
                        success: function (response) {
                            if (response.status) {
                                swal.fire({
                                    title: "@lang('general.success')",
                                    text: response.message,
                                    icon: "success",
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    timer: 3000
                                })
                                window.location = "{{route('admin.notes.categories')}}";
                            } else {
                                swal.fire({
                                    title: "@lang('general.error')",
                                    text: response.message,
                                    icon: "error",
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    timer: 3000
                                })
                            }
                        },
                        error: function (xhr) {
                            swal.fire({
                                title: "Error",
                                text: xhr.responseJSON.message,
                                icon: "error",
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 3000
                            })
                        }
                    });
                }
            });
        }

        $(document).ready(function () {
            $("#name").keyup(function () {
                let name = $(this).val();
                $("#slug").val(ToSeoUrl(name));
            });
            $("#category_create_form").submit(function () {
                $.ajax({
                    url: "{{request()->url()}}",
                    type: "POST",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status) {
                            swal.fire({
                                title: "@lang('general.success')",
                                text: response.message,
                                icon: "success",
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1500
                            })
                            location.reload();
                        } else {
                            swal.fire({
                                title: "@lang('general.error')",
                                text: response.message,
                                icon: "error",
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1500
                            })
                        }
                    },
                    error: function (xhr) {
                        swal.fire({
                            title: "Error",
                            text: xhr.responseJSON.message,
                            icon: "error",
                            showCancelButton: false,
                            showConfirmButton: false,
                            timer: 1500
                        })
                    }
                });
            });
        });
    </script>
@endsection
