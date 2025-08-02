<canvas id="canvas" width=852 height=480 style="position: absolute; top: 0; left: 0; background-color: transparent; z-index: 1;"></canvas>
<div
    x-data="boxesComponent('videoContainer_html5_api', {!! Js::from($object) !!})"
    @disable-drawing.document="onDisableDrawing"
    @enable-drawing.document="onEnableDrawing"
    @bbox-create.document="onBBoxCreate"
    @bbox-drawn.document="onBBoxDrawn"
    @bbox-change-blocked.document="onBBoxChangeBlocked"
    @video-update-state.document="onVideoUpdateState"
    @tracking-start.document="onStartTracking"
    @tracking-stop.document="onStopTracking"
    id="boxesContainer"
    style="position: absolute; top: 0; left: 0; width:852px; height:480px; background-color: transparent"
>
    <div
        class="bbox" style="display:none"
    >
        <div class="objectId">{{$object->idDynamicObject}}</div>
    </div>
</div>
