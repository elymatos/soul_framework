<x-layout::index>
    <script src="/scripts/utils/jquery.parser.js"></script>
    <script src="/scripts/utils/jquery.draggable.js"></script>
    <script src="/scripts/utils/jquery.resizable.js"></script>
    <script type="text/javascript" src="/annotation/deixis/script/objects"></script>
    <script type="text/javascript" src="/annotation/deixis/script/components"></script>
    <div class="app-layout annotation-deixis">
        <div class="annotation-header">
            <div class="flex-container between">
                <div class="flex-item">
                    <x-ui::breadcrumb
                        :sections="[['/','Home'],['/annotation/deixis','Deixis Annotation'],['',$document->name]]"></x-ui::breadcrumb>
                </div>
            </div>
        </div>
        <div class="annotation-canvas">
            <div class="annotation-video">
                <div class="annotation-player">
                @include("Annotation.Deixis.Panes.videoPane")
                    @include("Annotation.Deixis.Panes.navigationPane")
                </div>
                <div class="annotation-forms">
                    @include("Annotation.Deixis.Panes.formsPane")
                </div>
            </div>
            <div class="annotation-objects">
                @include("Annotation.Deixis.Panes.gridsPane")
            </div>
        </div>
    </div>
</x-layout::index>
