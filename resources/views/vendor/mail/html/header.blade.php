@props(['url' => config('app.url'), 'logo' => null])
@php($logo = $logo ?? asset('img/logo2.jpeg'))
<table class="header" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:20px;">
    <tr>
        <td style="text-align: center;">
            <a href="{{ $url }}" style="display:inline-block;">
                <img src="{{ $logo }}" alt="{{ config('app.name') }}" style="max-height:80px; width:auto; display:block; margin:0 auto;" />
            </a>
        </td>
    </tr>
</table>
