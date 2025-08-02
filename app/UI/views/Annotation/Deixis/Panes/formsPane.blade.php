<div
    id="formsPane"
    x-data="formsComponent({{$idDocument}})"
    @video-update-state.document="onVideoUpdateState"
    @object-selected.document="onObjectSelected"
>
    @if ($idDynamicObject == 0)
        @include("Annotation.Deixis.Forms.formNewObject")
    @else
        <div
            hx-trigger="load"
            hx-target="#formsPane"
            hx-get="/annotation/deixis/object"
            hx-vals='{"idDynamicObject": {{$idDynamicObject}} }'
            hx-swap="innerHTML"
        ></div>
    @endif
</div>
