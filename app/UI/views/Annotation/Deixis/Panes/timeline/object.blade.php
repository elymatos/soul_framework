@php
    $bgColor = $objectData->bgColor;
    $fgColor = $objectData->fgColor;
    $label = $objectData->name;
    $tooltip = "#" . $objectData->idDynamicObject . ": " . $label . "\nFrames: " . $objectData->startFrame . "-" . $objectData->endFrame . "\nDuration: " . $duration . " frames";
    if ($objectData->textComment != '') {
        $label = "*" . $label;
    }
@endphp
<div
    data-id="{{$objectData->idDynamicObject}}"
    class="internal"
    id="o{{$objectData->idDynamicObject}}"
    style="background-color: {{ $bgColor }};color: {{$fgColor}}"
    title="{{ $tooltip }}"
>
    {{ $label }}
</div>
