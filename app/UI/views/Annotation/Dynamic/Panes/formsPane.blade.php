<div
    id="formsPane"
    x-data="formsComponent({{$idDocument}})"
    @video-update-state.document="onVideoUpdateState"
    @bbox-toggle-tracking.document="onBBoxToggleTracking"
    @bbox-drawn.document="onBBoxDrawn"
>
    @if ($idDynamicObject == 0)
        @include("Annotation.Dynamic.Forms.formNewObject")
    @else
        <div
            hx-trigger="load"
            hx-target="#formsPane"
            hx-get="/annotation/dynamic/object"
            hx-vals='{"idDynamicObject": {{$idDynamicObject}} }'
            hx-swap="innerHTML"
        ></div>
    @endif
</div>
