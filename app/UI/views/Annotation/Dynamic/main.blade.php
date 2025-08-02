<x-layout::index>
    <script src="/scripts/utils/jquery.parser.js"></script>
    <script src="/scripts/utils/jquery.draggable.js"></script>
    <script src="/scripts/utils/jquery.resizable.js"></script>
    <script type="text/javascript" src="/annotation/dynamic/script/objects"></script>
    <script type="text/javascript" src="/annotation/dynamic/script/components"></script>
    <div class="app-layout annotation-dynamic">
        <div class="annotation-header">
            <div class="flex-container between">
                <div class="flex-item">
                    <x-ui::breadcrumb
                        :sections="[['/','Home'],['/annotation/dynamic','Dynamic Annotation'],['',$document->name]]"></x-ui::breadcrumb>
                </div>
            </div>
        </div>
        <input type="hidden" id="_token" value="{{ csrf_token() }}" />
        <div class="annotation-canvas">
            <div class="annotation-video">
                <div class="annotation-player">
                @include("Annotation.Dynamic.Panes.videoPane")
                </div>
                <div class="annotation-forms">
                    @include("Annotation.Dynamic.Panes.formsPane")
                </div>
            </div>
            <div class="annotation-objects">
                @include("Annotation.Dynamic.Panes.gridsPane")
            </div>
        </div>
    </div>
</x-layout::index>
