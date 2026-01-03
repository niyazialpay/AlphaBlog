@extends('panel.auth.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('auth.verify_email.subject') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('auth.verify_email.fresh_resend') }}
                        </div>
                    @endif

                    {{ __('auth.verify_email.before_proceeding') }}
                    {{ __('auth.verify_email.you_didnt_receive') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('auth.verify_email.resend') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
