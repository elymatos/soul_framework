<div>
    <input type="hidden" id="idDynamicObject" value="{{$object->idDynamicObject}}"/>
    <div class="flex-container h-2-5 items-center justify-between bg-gray-300">
        <div>
            <h3 class="ui header">Object #{{$object->idDynamicObject}} - {{$object->nameLayerType}}</h3>
        </div>
        <div>
            <button
                class="ui tiny icon button"
                @click="$dispatch('video-seek-frame', {frameNumber: {{$object->startFrame}} })"
            >
                Go to StartFrame: {{$object->startFrame}}
            </button>
            <button
                class="ui tiny icon button"
                @click="$dispatch('video-seek-frame', {frameNumber: {{$object->endFrame}} })"
            >
                Go to EndFrame: {{$object->endFrame}}
            </button>
            <button
                class="ui tiny icon button"
                hx-post="/annotation/dynamic/cloneObject"
                hx-vals='js:{"idDocument":{{$object->idDocument}},"idDynamicObject":{{$object->idDynamicObject}}}'
            >
                Clone object
            </button>
            <button
                class="ui tiny icon button danger"
                @click.prevent="messenger.confirmDelete('Removing object #{{$object->idDynamicObject}}.', '/annotation/dynamic/{{$object->idDocument}}/{{$object->idDynamicObject}}')"
            >
                Delete Object
            </button>
            <button
                id="btnClose"
                class="ui tiny icon button"
                title="Close Object"
                @click="window.location.assign('/annotation/dynamic/{{$object->idDocument}}')"
            >
                <i class="close tiny icon"></i>
            </button>
        </div>
    </div>
    <div
        class="objectPane ui pointing secondary menu tabs mt-0"
    >
        <a
            class="item active"
            data-tab="edit-object"
            :class="isPlaying && 'disabled'"
        >Annotate object</a>
        <a
            class="item"
            data-tab="create-bbox"
            :class="isPlaying && 'disabled'"
        >BBox</a>
        <a
            class="item"
            data-tab="modify-range"
            :class="isPlaying && 'disabled'"
        >Modify range</a>
        <a
            class="item"
            data-tab="comment"
            :class="isPlaying && 'disabled'"
        ><i class="comment dots outline icon"></i>Comment</a>
    </div>
    <div
        class="gridBody"
        x-init="$('.menu .item').tab()"
    >
        <div
            class="ui tab h-full w-full active"
            data-tab="edit-object"
        >
            @include("Annotation.Dynamic.Forms.formAnnotation")
        </div>
        <div
            class="ui tab h-full w-full"
            data-tab="create-bbox"
        >
            @include("Annotation.Dynamic.Forms.formBBox")
        </div>
        <div
            class="ui tab h-full w-full"
            data-tab="modify-range"
        >
            @include("Annotation.Dynamic.Forms.formModifyRange")
        </div>
        <div
            class="ui tab h-full w-full"
            data-tab="comment"
        >
            @include("Annotation.Dynamic.Forms.formComment")
        </div>
    </div>
</div>

<div
    x-data="boxesComponent('videoContainer_html5_api', {!! Js::from($object) !!})"
    @disable-drawing.document="onDisableDrawing"
    @enable-drawing.document="onEnableDrawing"
    @bbox-create.document="onBBoxCreate"
    @bbox-created.document="onBBoxCreated"
    @bbox-change-blocked.document="onBBoxChangeBlocked"
    @video-update-state.document="onVideoUpdateState"
    @tracking-start.document="onStartTracking"
    @tracking-stop.document="onStopTracking"
    @bbox-toggle-tracking.document="onBBoxToggleTracking"
    id="boxesContainer"
    style="position: absolute; top: 0; left: 0; width:852px; height:480px; background-color: transparent"
    hx-swap-oob="true"
>
    <div
        class="bbox" style="display:none"
    >
        <div class="objectId">{{$object->idDynamicObject}}</div>
    </div>
</div>
