<x-layout::index>
    <script type="text/javascript" src="/annotation/fullText/script/components"></script>
    <div class="app-layout annotation-corpus">
        <div class="annotation-header">
            <div class="flex-container between">
                <div class="flex-item">
                    <x-ui::breadcrumb
                        :sections="[['/','Home'],['/annotation/fe','FullText Annotation'],['','#' . $idDocumentSentence]]"></x-ui::breadcrumb>
                </div>
            </div>
        </div>
        <div class="annotation-canvas">
            <div class="annotation-navigation">
                <div class="flex-container between">
                    <div class="tag">
                        <div class="ui label wt-tag-id">
                            Corpus: {{$corpus->name}}
                        </div>
                        <div class="ui label wt-tag-id">
                            Document: {{$document->name}}
                        </div>
                    </div>
                    <div>
                        @if($idPrevious)
                            <a href="/annotation/fe/sentence/{{$idPrevious}}">
                                <button class="ui left labeled icon button">
                                    <i class="left arrow icon"></i>
                                    Previous
                                </button>
                            </a>
                        @endif
                        @if($idNext)
                            <a href="/annotation/fe/sentence/{{$idNext}}">
                                <button class="ui right labeled icon button">
                                    <i class="right arrow icon"></i>
                                    Next
                                </button>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="annotation-workarea">
                <div class="flex-container wrap gap-0">
                    @foreach($tokens as $i => $token)
                        @php($hasAS = ($token['idAS'] != -1))
                        @if(!$token['hasLU'] && !$hasAS)
                            <div
                                class="ui medium button mb-2 hasNone"
                            >{{$token['word']}}</div>
                        @else
                            <div
                                class="ui medium button mb-2 {!! $hasAS ? 'hasAS' : 'hasLU' !!}"
                                hx-get="{!! $hasAS ? '/annotation/fullText/as/' . $token['idAS'] . '/' . $token['word'] : '/annotation/fullText/lus/'. $idDocumentSentence . '/'. $i !!}"
                                hx-target=".annotation-panel"
                                hx-swap="innerHTML"
                            >{{$token['word']}}
                            </div>
                        @endif
                    @endforeach
                </div>
                <div
                    class="annotation-panel"
                ></div>
                @if(!is_null($idAnnotationSet))
                    <div
                        hx-trigger="load"
                        hx-get="/annotation/fullText/as/{{$idAnnotationSet}}/{{$word}}"
                        hx-target=".annotation-panel"
                        hx-swap="innerHTML"
                    >
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout::index>
