<div
    x-data="objectComponent({!! Js::from($object) !!} ,'{{ csrf_token() }}')"
    @bbox-drawn.document="onBboxDrawn"
    @video-update-state.document="onVideoUpdateState"
>
    <div class="flex-container h-2-5 items-center justify-between bg-gray-300">
        <div>
            <h3 class="ui header">Object #{{$object->idDynamicObject}} - {{$object->nameLayerType}}</h3>
        </div>
        <div>
            <button
                class="ui tiny icon button"
                @click="gotoFrame({{$object->startFrame}})"
            >
                StartFrame: {{$object->startFrame}}
            </button>
            <button
                class="ui tiny icon button"
                @click="gotoFrame({{$object->endFrame}})"
            >
                EndFrame: {{$object->endFrame}}
            </button>
            <button
                class="ui tiny icon button danger"
                @click.prevent="messenger.confirmDelete('Removing object #{{$object->idDynamicObject}}.', '/annotation/deixis/{{$object->idDocument}}/{{$object->idDynamicObject}}')"
            >
                Delete Object
            </button>
            <button
                id="btnClose"
                class="ui tiny icon button"
                title="Close Object"
                @click="window.location.assign('/annotation/deixis/{{$object->idDocument}}')"
            >
                <i class="close tiny icon"></i>
            </button>
        </div>
    </div>
    <div
        class="objectPane ui pointing secondary menu tabs mt-0"
    >
        <a class="item" data-tab="edit-object" :class="isPlaying && 'disabled'">Annotate object</a>
        <a class="item" data-tab="create-bbox" :class="isPlaying && 'disabled'">BBox</a>
        <a class="item" data-tab="modify-range" :class="isPlaying && 'disabled'">Modify range</a>
        <a class="item" data-tab="comment" :class="isPlaying && 'disabled'"><i class="comment dots outline icon"></i>Comment</a>
    </div>
    <div class="gridBody">
        <div
            class="ui tab h-full w-full active"
            data-tab="edit-object"
        >
            @include("Annotation.Deixis.Forms.formAnnotation")
        </div>
        <div
            class="ui tab h-full w-full"
            data-tab="create-bbox"
        >
            @include("Annotation.Deixis.Forms.formBBox")
        </div>
        <div
            class="ui tab h-full w-full"
            data-tab="modify-range"
        >
            @include("Annotation.Deixis.Forms.formModifyRange")
        </div>
        <div
            class="ui tab h-full w-full"
            data-tab="comment"
        >
            @include("Annotation.Deixis.Forms.formComment")
        </div>
    </div>
    <script type="text/javascript">
        $(function() {
            $(".objectPane .item")
                .tab()
            ;
            document.dispatchEvent(new CustomEvent("video-seek-frame", { detail: { frameNumber: {{$object->startFrame}} } }));
        });
    </script>

</div>
