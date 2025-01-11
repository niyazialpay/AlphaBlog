@extends('panel.base')
@section('title', __('firewall.firewall'))

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            @lang('firewall.firewall')
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('firewall.rules')</h3>
        </div>
        <div class="card-body">
            <form class="row" action="{{route('admin.firewall')}}" method="post">
                @csrf
                <div class="col-12 mb-3">
                    <label for="is_active">@lang('ip_filter.status')</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" @if($firewall->is_active == 1) selected @endif>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if($firewall->is_active == 0) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="blacklist_rule_id">@lang('firewall.blacklist_rule_id')</label>
                    <select name="blacklist_rule_id" id="blacklist_rule_id" class="form-control">
                        <option value="">@lang('firewall.select_rule')</option>
                        @foreach($ipFilters as $ipFilter)
                            <option value="{{$ipFilter->id}}" @if($firewall->blacklist_rule_id == $ipFilter->id) selected @endif>
                                {{$ipFilter->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="whitelist_rule_id">@lang('firewall.whitelist_rule_id')</label>
                    <select name="whitelist_rule_id" id="whitelist_rule_id" class="form-control">
                        <option value="">@lang('firewall.select_rule')</option>
                        @foreach($ipFilters as $ipFilter)
                            <option value="{{$ipFilter->id}}" @if($firewall->whitelist_rule_id == $ipFilter->id) selected @endif>
                                {{$ipFilter->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="check_referer">@lang('firewall.check_referer')</label>
                    <select name="check_referer" id="check_referer" class="form-control">
                        <option value="1" @if($firewall->check_referer == 1) selected @endif>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if($firewall->check_referer == 0) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                <div class="col-4 mb-3">
                    <label for="check_bots">@lang('firewall.check_bots')</label>
                    <select name="check_bots" id="check_bots" class="form-control">
                        <option value="1" @if($firewall->check_bots == 1) selected @endif>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if($firewall->check_bots == 0) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                <div class="col-8 mb-3">
                    <label for="bad_bots">@lang('firewall.bad_bots')</label>
                    <input type="text" name="bad_bots" id="bad_bots" class="form-control" value="{{old('bad_bots', $firewall->bad_bots)}}">
                </div>
                <div class="col-12 mb-3">
                    <label for="check_request_method">@lang('firewall.check_request_method')</label>
                    <select name="check_request_method" id="check_request_method" class="form-control">
                        <option value="1" @if($firewall->check_request_method == 1) selected @endif>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if($firewall->check_request_method == 0) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="check_dos">@lang('firewall.check_dos')</label>
                    <select name="check_dos" id="check_dos" class="form-control">
                        <option value="1" @if($firewall->check_dos == 1) selected @endif>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if($firewall->check_dos == 0) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="check_union_sql">@lang('firewall.check_union_sql')</label>
                    <select name="check_union_sql" id="check_union_sql" class="form-control">
                        <option value="1" @if($firewall->check_union_sql == 1) selected @endif>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if($firewall->check_union_sql == 0) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="check_click_attack">@lang('firewall.check_click_attack')</label>
                    <select name="check_click_attack" id="check_click_attack" class="form-control">
                        <option value="1" @if($firewall->check_click_attack == 1) selected @endif>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if($firewall->check_click_attack == 0) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="check_xss">@lang('firewall.check_xss')</label>
                    <select name="check_xss" id="check_xss" class="form-control">
                        <option value="1" @if($firewall->check_click_attack == 1) selected @endif>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if($firewall->check_click_attack == 0) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="check_cookie_injection">@lang('firewall.check_cookie_injection')</label>
                    <select name="check_cookie_injection" id="check_cookie_injection" class="form-control">
                        <option value="1" @if($firewall->check_click_attack == 1) selected @endif>
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if($firewall->check_click_attack == 0) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i></button>
                </div>
            </form>
        </div>
    </div>
@endsection
