@extends('panel.base')
@section('title',__('categories.categories'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.posts', ['type' => 'blogs'])}}">@lang('post.blogs')</a>
        </li>
        <li class="breadcrumb-item active">@lang('categories.categories')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-pills">
                @foreach(app('languages') as $n => $language)
                    <li class="nav-item">
                        <a class="nav-link @if($n==0)active @endif" href="#{{$language->code}}" data-bs-toggle="tab">
                            {{$language->name}}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6  table-responsive border-end-1">
                    <div class="tab-content">
                        @foreach(app('languages') as $n => $language)
                            <div class="tab-pane @if($n==0) active @endif" id="{{ $language->code }}">
                                <table class="table table-striped" aria-describedby="@lang('categories.categories')">
                                    <thead>
                                    <tr>
                                        <th scope="@lang('categories.image')">@lang('categories.image')</th>
                                        <th scope="@lang('categories.name')">@lang('categories.name')</th>
                                        <th scope="@lang('general.actions')" style="width: 200px;">@lang('general.actions')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $categoriesList = \App\Models\Post\Categories::with('children')->where('parent_id', null)->where('language', $language->code)->get();
                                    @endphp
                                    @forelse($categoriesList as $categories)
                                        @include('panel.post.category.partials.category_row', ['categories' => $categories, 'level' => 0])
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">
                                                @lang('categories.error_not_found')
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th scope="@lang('categories.image')">@lang('categories.image')</th>
                                        <th scope="@lang('categories.name')">@lang('categories.name')</th>
                                        <th scope="@lang('general.actions')">@lang('general.actions')</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-6">
                    <a href="{{route('admin.categories')}}"
                       data-bs-toggle="tooltip" data-bs-placement="top"
                          title="@lang('general.new')"
                       class="btn btn-sm btn-primary">
                        <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-plus"></i> @lang('general.new')
                    </a>
                    <form class="row" method="post" id="category_create_form" action="javascript:void(0);" enctype="multipart/form-data">
                        <div class="col-12 mb-3">
                            <label for="name">@lang('categories.name')</label>
                            <input type="text" class="form-control" name="name" id="name"
                                   placeholder="@lang('categories.name_placeholder')" value="{{$category->name}}">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="slug">@lang('categories.slug')</label>
                            <input type="text" class="form-control" name="slug" id="slug"
                                   placeholder="@lang('categories.slug_placeholder')" value="{{$category->slug}}">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="language">@lang('general.language')</label>
                            <select name="language" id="language" class="form-control">
                                @foreach(app('languages') as $language)
                                    <option value="{{$language->code}}"
                                            @if($language->code == $lng) selected @endif>{{$language->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="parent_id">@lang('categories.parent_category')</label>
                            <select name="parent_id" id="parent_id" class="form-control">
                                <option value="">@lang('categories.parent_category')</option>
                                @foreach($categories->where('language', $lng)->get() as $item)
                                    <option value="{{$item->id}}"
                                            @if($category->parent_id == $item->id) selected @endif>{{$item->name}} ({{$item->language}})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3" id="image_input">
                            @if($category->getFirstMediaUrl('categories', 'thumb'))
                                <label for="image">@lang('post.image')</label>
                                <img src="{{$category->getFirstMediaUrl('categories', 'thumb')}}" id="image"
                                     alt="{{stripslashesNull($category->title)}}" class="img-fluid" width="150">
                                <a href="javascript:imageDelete('{{$category->id}}')" class="text-danger">
                                    <i class="fa fa-trash"></i>
                                </a>
                            @else
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="image" id="image" accept="image/*">
                                        <label class="custom-file-label" for="image">@lang('post.image')</label>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-12 mb-3">
                            <label for="meta_keywords">@lang('categories.meta_keywords')</label>
                            <input type="text" class="form-control" name="meta_keywords" id="meta_keywords"
                                   placeholder="@lang('categories.meta_keywords_placeholder')"
                                   value="{{$category->meta_keywords}}">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="meta_description">@lang('categories.meta_description')</label>
                            <input type="text" class="form-control" name="meta_description" id="meta_description"
                                   placeholder="@lang('categories.meta_description_placeholder')"
                                   value="{{$category->meta_description}}">
                        </div>
                        <div class="col-12 mb-3 border rounded p-3">
                            <ul class="nav nav-pills border-bottom">
                                @foreach(app('languages') as $n => $language)
                                <li class="nav-item"><a class="nav-link @if($n==0) active @endif " href="#form_{{$language->code}}" data-bs-toggle="tab">{{$language->name}}</a></li>
                                @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach(app('languages') as $n => $language)

                                <div class="tab-pane @if($n==0) active @endif" id="form_{{$language->code}}">
                                    <input type="hidden" name="hreflang[{{$language->code}}]" value="{{$category->hreflang[$language->code] ?? ''}}">
                                    <label for="hreflang_url[{{$language->code}}]">Href Lang ({{$language->code}})</label>

                                    <input type="text" class="form-control" name="hreflang_url[{{$language->code}}]" id="hreflang_url[{{$language->code}}]"
                                           placeholder="Href Lang ({{$language->code}})"
                                           @if($category->href_lang)
                                           @if(array_key_exists($language->code, json_decode($category->href_lang, true))) value="{{json_decode($category->href_lang, true)[$language->code]}}" @endif @endif>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @csrf
                        <input type="hidden" name="id" value="{{$category->id}}">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                @lang('general.save')
                            </button>
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
                        url: "{{route('admin.categories.delete')}}",
                        type: "POST",
                        data: {
                            id: id,
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
                                    timer: 1500
                                })
                                //window.location = "{{route('admin.categories')}}";
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
                }
            });
        }

        function imageDelete(id){
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
                        url: "{{route('admin.categories.image.delete')}}",
                        type: "POST",
                        data: {
                            id: id,
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
                }
            });
        }

        $(document).ready(function () {
            $("#name").keyup(function () {
                let name = $(this).val();
                $("#slug").val(ToSeoUrl(name));
            });

            $('#language').change(function(){
                $('#parent_id').html('<option value="">@lang('categories.parent_category')</option>');
                $.ajax({
                    url: '{{route('admin.categories.list')}}',
                    type: 'POST',
                    data: {
                        _token: '{{csrf_token()}}',
                        language: $(this).val()
                    },
                    success: function(data){
                        $.each(data, function (index, value) {
                            $('#parent_id').append('<option value="'+value.id+'">'+value.name+' (' + value.language + ')</option>');
                        });
                    }
                });
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
