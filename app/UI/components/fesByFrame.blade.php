@props([
    'idFrame' => 0,
    'label' => 'FE',
    'name' => 'idFrameElement',
    'value' => null,
    'defaultText' => 'Select FE'
])

@php

    use App\Database\Criteria;

    $value = $value ?? $nullName ?? '';
    $options = [];
    if ($idFrame > 0) {
        $filter = [["idFrame", "=", $idFrame]];
        if (isset($coreType)) {
            $filter[] = ["coreType", "IN", $coreType];
        }
        $fes = Criteria::byFilterLanguage("view_frameelement", $filter)->all();
        if (isset($hasNull)) {
            $options[] = [
                'idFrameElement' => '-1',
                'name' => $nullName ?? "NULL",
                'coreType' => '',
                'idColor' => "color_1"
            ];
        }
        foreach ($fes as $fe) {
            if ($value == $fe->idFrameElement) {
                $defaultText = $fe->name;
            }
            $options[] = [
                'idFrameElement' => $fe->idFrameElement,
                'name' => $fe->name,
                'coreType' => $fe->coreType,
                'idColor' => $fe->idColor
            ];
        }
    }
@endphp

@if($label!='')
    <label>{{$label}}</label>
@endif
<div
    class="ui floating medium dropdown frameElement border rounded"
    style="overflow:initial;"
    x-init="$($el).dropdown()"
>
    <input type="hidden" name="{{$name}}" value="{{$value}}">
{{--    <i class="dropdown icon"></i>--}}
    <div class="text">{!! ($idFrame == 0) ? 'No frame selected' : ($defaultText ?? '') !!}</div>
    <div class="menu">
        @foreach($options as $fe)
            <div
                data-value="{{$fe['idFrameElement']}}"
                class="item"
            >
                @if($fe['coreType'] != '')
                    @php
                        $icon = config("webtool.fe.icon")[$fe['coreType']]
                    @endphp
                <div
                    class="fe color_{{$fe['idColor']}}"
                >
                    <i class="{{$icon}} icon" style="visibility: visible;font-size:0.875em"></i>
                    {{$fe['name']}}
                </div>
                @else
                    <div
                        class="fe color_{{$fe['idColor']}}"
                    >
                    <span>{{$fe['name']}}</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
