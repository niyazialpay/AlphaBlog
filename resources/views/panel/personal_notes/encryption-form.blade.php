@extends('panel.base')
@section('title',__('notes.notes'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            @lang('notes.notes')
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <form class="modal-content" id="encryptionForm" method="post" action="javascript:void(0)">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('notes.encryption_key')</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <label for="encryption_key">@lang('notes.encryption_key')</label>
                            <input type="password" class="form-control"
                                   id="encryption_key" name="encryption_key"
                                   placeholder="@lang('notes.encryption_key')">
                        </div>
                        <div class="col-12">
                            <label for="remember_time">@lang('notes.remember_time')</label>
                            <select class="form-control" id="remember_time" name="remember_time">
                                <option value="1">
                                    @lang('notes.remember_time_1_day')
                                </option>

                                <option value="30">
                                    {{trans_choice('notes.remember_time_month', 1, ['n' => 1])}}
                                </option>

                                <option value="90">
                                    {{trans_choice('notes.remember_time_month', 3, ['n' => 3])}}
                                </option>

                                <option value="180">
                                    {{trans_choice('notes.remember_time_month', 6, ['n' => 6])}}
                                </option>

                                <option value="365">
                                    @lang('notes.remember_time_1_year')
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                @csrf
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            @if(!request()->cookie('encryption_key'))
            $('#encryption_key_modal').modal('show');
            @endif
            $('#encryptionForm').submit(function(){
                $.ajax({
                    url: '{{route('admin.notes.encryption')}}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#encryption_key_modal').modal('hide');
                        Swal.fire({
                            title: '@lang('general.saved')',
                            text: '@lang('notes.encryption_key_saved')',
                            icon: 'success',
                            confirmButtonText: '@lang('general.ok')',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        })
                    }
                })
            });
        });
    </script>

@endsection
