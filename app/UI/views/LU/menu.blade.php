@php
    $items = [
        ['formEdit','Edit'],
        ['qualia','Qualia'],
        ['constraints','Constraints'],
        ['semanticTypes','SemanticTypes'],
    ];
$id = uniqid("luMenu")
@endphp
<x-objectmenu
    id="{{$id}}"
    :items="$items"
    :path="'/lu/' . $lu->idLU"
></x-objectmenu>
