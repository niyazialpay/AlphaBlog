@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
    <img src="{{$general_settings->getFirstMediaUrl('site_logo_light')}}" class="logo" alt="{{config('app.name')}}">
</a>
</td>
</tr>
