<div
    class="h-full"
>
    <div class="ui card w-full">
        <div class="content">
            <div class="header">
                <div class="flex-container between">
                    <div>
                        LU: <span class="color_frame">{{$lu->frame->name}}</span>.{{$lu->name}}
                    </div>
                    <div class="text-right">
                        <div class="ui small compact menu">
                            <div class="ui simple dropdown item">
                                Alternative LUs
                                <i class="dropdown icon"></i>
                                <div class="menu">
                                    @foreach($alternativeLU as $lu)
                                        <div class="item">{{$lu->frameName}}.{{$lu->lu}}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="ui label wt-tag-id">
                            #{{$idAnnotationSet}}
                        </div>
                        <button
                            class="ui button negative"
                            onclick="messenger.confirmDelete(`Removing AnnotationSet #{{$idAnnotationSet}}'.`, '/annotation/fe/annotationset/{{$idAnnotationSet}}', null, '#workArea')"
                        >
                            Delete this AnnotationSet
                        </button>

                    </div>
                </div>
            </div>
            <hr>
            <div
                x-data="annotationSetComponent({{$idAnnotationSet}},'{{$word}}')"
                @selectionchange.document="selectionRaw =  document.getSelection()"
                @change-label-tab.document="onChangeLabelTab"
                class="h-full"
            >
                <div class="annotationSet">
                    @include("Annotation.FullText.Panes.asAnnotation")
                </div>
                @include("Annotation.FullText.Panes.asLabels")
            </div>
        </div>
    </div>
</div>
