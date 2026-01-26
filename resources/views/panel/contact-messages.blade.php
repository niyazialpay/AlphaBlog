@extends('panel.base')
@section('title', __('contact.messages'))

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">@lang('contact.messages')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('contact.messages')</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="contact-messages" class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">@lang('contact.name_surname')</th>
                        <th scope="col">@lang('contact.email')</th>
                        <th scope="col">@lang('contact.subject')</th>
                        <th scope="col">@lang('contact.message')</th>
                        <th scope="col">@lang('general.created_at')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td>
                                <strong>{{ $message->name }}</strong>
                                @if($message->language)
                                    <br>
                                    <small class="text-muted">@lang('language.language'): {{ strtoupper($message->language) }}</small>
                                @endif
                                @if($message->ip_address)
                                    <br>
                                    <small class="text-muted">@lang('sessions.ip_address'): {{ $message->ip_address }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                            </td>
                            <td>{{ $message->subject }}</td>
                            <td style="white-space: pre-wrap;">{!! nl2br(e($message->message)) !!}</td>
                            <td>{{ dateformat($message->created_at, 'd M. Y D. H:i:s', timezone: config('app.timezone')) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">@lang('contact.no_messages')</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
@endsection
