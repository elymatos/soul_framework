@php
    $icon = config("webtool.fe.icon")[$type]
@endphp
<span {{$attributes->merge(['class' => 'fe color_'. $idColor])}}>
    <span style="display:inline-block;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><i class="{{$icon}} icon" style="visibility: visible;font-size:0.875em"></i>{{$name}}</span>
</span>
