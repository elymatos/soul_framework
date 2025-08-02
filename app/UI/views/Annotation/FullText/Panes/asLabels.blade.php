<div class="annotationTab">
    <div class="ui pointing secondary menu tabs">
        @foreach($layerTypes as $layerType)
            <a class="item" data-tab="{{$layerType->entry}}">{{$layerType->name}}</a>
        @endforeach
            <a class="item" data-tab="comment"><i class="comment dots outline icon"></i>Comment</a>
    </div>
    <div class="gridBody">
        @foreach($labels as $layerType => $labelData)
            @if($layerType == 'lty_fe')
                <div
                    class="ui tab active"
                    data-tab="lty_fe"
                >
                    @foreach($fesByType as $type => $fesData)
                        @if (count($fesData) > 0)
                            <div>{{$type}}</div>
                            <div class="rowFE">
                                @foreach($fesData as $fe)
                                    <div class="colFE">
                                        <button
                                            class="ui right labeled icon button mb-2 color_{{$fe->idColor}}"
                                            @click.stop="onLabelAnnotate({{$fe->idEntity}},'lty_fe')"
                                        >
                                            <i
                                                class="delete icon"
                                                @click.stop="onLabelDelete({{$fe->idEntity}},'lty_fe')"
                                            >
                                            </i>
                                            <div class="d-flex">
                                                <i class="{!! config("webtool.fe.icon")[$fe->coreType] !!} icon text-small"></i>{{$fe->name}}
                                            </div>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div
                    class="ui tab"
                    data-tab="{{$layerType}}"
                >
                    <div class="rowFE">
                        @foreach($labelData as $idEntity => $label)
                            <div class="colFE">
                                <button
                                    class="ui right labeled icon button mb-2 color_{{$label->idColor}}"
                                    @click.stop="onLabelAnnotate({{$idEntity}},'{{$layerType}}')"
                                >
                                    <i
                                        class="delete icon"
                                        @click.stop="onLabelDelete({{$idEntity}},'{{$layerType}}')"
                                    >
                                    </i>
                                    <div class="d-flex">
                                        {{$label->name}}
                                    </div>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
        <div class="ui tab" data-tab="comment">
             @include("Annotation.FE.Forms.formComment")
        </div>
    </div>
    <script type="text/javascript">
        $(".tabs .item")
            .tab()
        ;
    </script>
</div>
