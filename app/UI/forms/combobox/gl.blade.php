@props([
    'idGenericLabel' => 0,
    'idLayerType' => 0,
    'label' => '',
    'name' => 'idGenericLabel',
    'value' => null,
    'defaultText' => 'Select Label',
    'hasNull' => false
])

@php
    use App\Database\Criteria;

    $value = $value ?? $this->nullName ?? '';
    $options = [];
    if ($idLayerType > 0) {
        $filter = [["idLayerType", "=", $idLayerType]];
        $gls = Criteria::byFilterLanguage("genericlabel", $filter)->all();
        if ($hasNull) {
            $options[] = [
                'idGenericLabel' => '-1',
                'name' => $nullName ?? "NULL",
                'idColor' => "color_1"
            ];
        }
        foreach ($gls as $gl) {
            if ($value == $gl->idGenericLabel) {
                $default = $gl->name;
            }
            $options[] = [
                'idGenricLabel' => $gl->idGenericLabel,
                'name' => $gl->name,
                'idColor' => $gl->idColor
            ];
        }
    }
@endphp

@if($label!='')
    <label for="{{$name}}">{{$label}}</label>
@endif
<div
    class="ui selection dropdown frameElement"
    style="overflow:initial;"
    x-init="$($el).dropdown()"
>
    <input type="hidden" name="{{$name}}" value="{{$value}}">
    <i class="dropdown icon"></i>
    <div class="default text">{{$defaultText ?? ''}}</div>
    <div class="menu">
        @foreach($options as $option)
            <div data-value="{{$option['idGenricLabel']}}"
                 class="item">
                <x-element.gl name="{{$option['name']}}" idColor="{{$option['idColor']}}"></x-element.gl>
            </div>
        @endforeach
    </div>
</div>
