@props([
    'label' => '',
    'name' => 'idLayerType',
    'value' => 0
])

@php
    use App\Database\Criteria;
    use App\Services\AppService;

    $list = Criteria::table("view_layertype as lt")
            ->join("layergroup as lg", "lg.idLayerGroup", "=", "lt.idLayerGroup")
            ->select("lt.idLayerType as id", "lt.name as text")
            ->where("lg.type", "Deixis")
            ->where("lt.idLanguage", AppService::getCurrentIdLanguage())
            ->orderBy("lg.name")
            ->orderBy("lt.name")
            ->all();
@endphp

@if($label != '')
    <label for="{{$name}}">{{$label}}</label>
@endif
<div
    class="ui selection clearable dropdown"
    style="overflow:initial"
    x-init="$($el).dropdown()"
>
    <input type="hidden"  name="{{$name}}" value="{{$value}}">
    <i class="dropdown icon"></i>
    <div class="medium default text">Select layer</div>
    <div class="ui menu">
        @foreach($list as $option)
            <div
                data-value="{{$option->id}}"
                class="item"
            >
                {{$option->text}}
            </div>
        @endforeach
    </div>
</div>
