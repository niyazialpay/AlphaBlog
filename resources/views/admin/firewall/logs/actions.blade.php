<div class="d-block">
    @if($log?->ipList?->ip == $log->ip && $log?->ipList?->filter->list_type == 'blacklist')
        <a href="javascript:addToWhitelist('{{$log->ip}}')"
           class="btn btn-sm btn-primary mx-1 my-2"
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="@lang('firewall.add_to_whitelist')">
            <i class="fa-solid fa-plus"></i>
        </a>
        <a href="javascript:deleteFromBlacklist('{{$log->ip}}')"
           class="btn btn-sm btn-danger mx-1 my-2"
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="@lang('firewall.remove_from_blocklist')">
            <i class="fa fa-trash"></i>
        </a>
    @endif
</div>
