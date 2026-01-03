<div class="tab-pane" id="two-fa-tab">
    @if(auth()->user()->two_factor_confirmed_at)
        <div class="col-12">
            <div class="row">
                <div class="col-12">
                    <h3>{{__('Recovery codes')}}</h3>
                </div>
                @foreach(auth()->user()->recoveryCodes() as $codes)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mt-1 mb-1">
                        <div class="row justify-content-center">
                            <div class="col-11 bg-bitbucket rounded p-1">
                                {{$codes}}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <form action="{{route('two-factor.disable')}}" class="mt-2" method="post">
            @csrf
            @method('delete')
            <button type="submit" class="btn btn-danger">@lang('profile.deactivate_2fa')</button>
        </form>
        <!-- 2FA enabled but not yet confirmed, we show the QRcode and ask for confirmation : -->
    @elseif(auth()->user()->two_factor_secret)
        <form action="javascript:void(0);" id="two-factor-confirm" class="row" method="post">
            @csrf
            <div class="col-md-6 col-12">
                {!! auth()->user()->twoFactorQrCodeSvg() !!}
                <br>
                {{decrypt(auth()->user()->two_factor_secret)}}
            </div>
            <div class="col-md-6 col-12">
                <div class="mb-1">
                    <label class="form-label" for="code">{{__('OTP Code')}}</label>
                    <input type="text" id="code" class="form-control" placeholder="{{__('OTP Code')}}" name="code" required>
                </div>
                <button type="submit" class="btn btn-primary">@lang('profile.validate_2fa')</button>
            </div>

        </form>
        <!-- 2FA not enabled at all, we show an 'enable' button  : -->
    @else
        <form action="{{route('two-factor.enable')}}" method="post">
            @csrf
            <button type="submit" class="btn btn-primary">@lang('profile.active_2fa')</button>
        </form>
    @endif
</div>
