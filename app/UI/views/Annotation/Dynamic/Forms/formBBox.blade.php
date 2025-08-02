<div class="flex-container between">
    <div class="flex-container">
        <div  x-show="currentFrame === {!! $object->startFrame !!}">
            <button
                id="btnCreateObject"
                class="ui button primary {!! $object->hasBBoxes ? 'disabled' : '' !!}"
                @click="$dispatch('bbox-create')"
            >
                <i class="plus square outline icon"></i>
                Create BBox
            </button>
        </div>
        <div class="flex-container" x-show="bboxDrawn">
            <div>
                <button
                    class="ui button primary toggle {!! $object->hasBBoxes ? '' : 'disabled' !!}"
                    @click="$dispatch('bbox-toggle-tracking')"
                >
                    <i :class="isTracking ? 'stop icon' : 'play icon'"></i>
                    <span x-text="isTracking ? 'Stop' : 'Track'"></span>
                </button>
            </div>
            <div>
                <div
                    class="ui checkbox"
                    x-init="$($el).checkbox()"
                    @click="$dispatch('bbox-change-blocked')"
                >
                    <input
                        type="checkbox"
                        tabindex="0"
                        :checked="bboxDrawn && (bboxDrawn.blocked === 1)"
                    >
                    <label class="pl-6">is blocked?</label>
                </div>
            </div>
        </div>
    </div>
    <div>
        <button
            id="btnDeleteBBox"
            class="ui medium icon button negative"
            :class="isTracking && 'disabled'"
            title="Delete BBoxes from Object"
            @click.prevent="messenger.confirmDelete('Removing all BBoxes of object #{{$object->idDynamicObject}}.', '/annotation/dynamic/deleteAllBBoxes/{{$object->idDocument}}/{{$object->idDynamicObject}}')"
        >
            <i class="trash alternate outline icon"></i>
            Delete All BBoxes
        </button>
    </div>
</div>
<div class="flex-container pt-3">
    <div>Current BBox: <span x-text="bboxDrawn ? '#' + bboxDrawn.idBoundingBox : 'none' "></span></div>
    <div x-text="bboxDrawn && 'x: ' + bboxDrawn.x"></div>
    <div x-text="bboxDrawn && 'y: ' + bboxDrawn.y"></div>
    <div x-text="bboxDrawn && 'width: ' + bboxDrawn.width"></div>
    <div x-text="bboxDrawn && 'height: ' + bboxDrawn.height"></div>
</div>
