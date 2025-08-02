<x-layout::index>
    <div class="app-layout">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title">
                        <x-ui::element.frame name="{{$frame->name}}"></x-ui::element.frame>
                    </div>
                    <div class="page-subtitle">{!! str_replace('ex>','code>',nl2br($frame->description)) !!}</div>
                </div>
            </div>
            <div class="page-content">
                <div class="content-container">
                    <h1 class="ui header">Frame Elements</h1>
                    <h2 class="ui header" id="core"><a href="#core">Core</a></h2>
                    @foreach ($fe['core'] as $feObj)
                        <div class="ui card fluid data-card"
                             data-entity-id="{{$feObj->idFrameElement}}">
                            <div class="content">
                                <div class="header">
                                    <x-ui::element.fe name="{{$feObj->name}}" type="cty_core"
                                                      :idColor="$feObj->idColor"></x-ui::element.fe>
                                </div>
                                <div class="description">
                                    {!! $feObj->description !!}
                                </div>
                            </div>
                            @if($feObj->relations)
                                <div class="extra content">
                                    <div class="data-card-info">
                                        @foreach ($feObj->relations as $relation)
                                            <b>{{$relation['name']}}:&nbsp;</b>{{$relation['relatedFEName']}}
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if(isset($fe['semanticTypes'][$feObj->idFrameElement]))
                                <div class="extra content">
                                    <div class="data-card-tags">
                                        <div
                                            class="ui label semantictype">{{$fe['semanticTypes'][$feObj->idFrameElement]->name ?? ''}}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                    @if ($fe['core_unexpressed'])
                        <h2 class="ui header" id="core-unexpressed""><a href="#core-unexpressed">Core
                            Unexpressed</a></h2>
                        @foreach ($fe['core_unexpressed'] as $feObj)
                            <div class="ui card fluid data-card"
                                 data-entity-id="{{$feObj->idFrameElement}}">
                                <div class="content">
                                    <div class="header">
                                        <x-ui::element.fe name="{{$feObj->name}}" type="cty_core"
                                                          :idColor="$feObj->idColor"></x-ui::element.fe>
                                    </div>
                                    <div class="description">
                                        {!! $feObj->description !!}
                                    </div>
                                </div>
                                @if($feObj->relations)
                                    <div class="extra content">
                                        <div class="data-card-info">
                                            @foreach ($feObj->relations as $relation)
                                                <b>{{$relation['name']}}
                                                    :&nbsp;</b>{{$relation['relatedFEName']}}
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if(isset($fe['semanticTypes'][$feObj->idFrameElement]))
                                    <div class="extra content">
                                        <div class="data-card-tags">
                                            <div
                                                class="ui label semantictype">{{$fe['semanticTypes'][$feObj->idFrameElement]->name ?? ''}}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                    @if ($fecoreset)
                        <h3 class="ui header">FE Core set(s)</h3>
                        <div class="ui fluid card data-card">
                            <div class="content">{{$fecoreset}}</div>
                        </div>
                    @endif
                    @if ($fe['peripheral'])
                        <h2 class="ui header" id="peripheral"><a href="#peripheral">Peripheral</a></h2>
                        @foreach ($fe['peripheral'] as $feObj)
                            <div class="ui card fluid data-card"
                                 data-entity-id="{{$feObj->idFrameElement}}">
                                <div class="content">
                                    <div class="header">
                                        <x-ui::element.fe name="{{$feObj->name}}" type="cty_core"
                                                          :idColor="$feObj->idColor"></x-ui::element.fe>
                                    </div>
                                    <div class="description">
                                        {!! $feObj->description !!}
                                    </div>
                                </div>
                                @if(isset($fe['semanticTypes'][$feObj->idFrameElement]))
                                    <div class="extra content">
                                        <div class="data-card-tags">
                                            <div
                                                class="ui label semantictype">{{$fe['semanticTypes'][$feObj->idFrameElement]->name ?? ''}}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                    @if ($fe['extra_thematic'])
                        <h3 class="ui header" id="extra-thematic"><a href="#extra-thematic">Extra-thematic</a></h3>
                        @foreach ($fe['extra_thematic'] as $feObj)
                            <div class="ui card fluid data-card"
                                 data-entity-id="{{$feObj->idFrameElement}}">
                                <div class="content">
                                    <div class="header">
                                        <x-ui::element.fe name="{{$feObj->name}}" type="cty_core"
                                                          :idColor="$feObj->idColor"></x-ui::element.fe>
                                    </div>
                                    <div class="description">
                                        {!! $feObj->description !!}
                                    </div>
                                </div>
                                @if(isset($fe['semanticTypes'][$feObj->idFrameElement]))
                                    <div class="extra content">
                                        <div class="data-card-tags">
                                            <div
                                                class="ui label semantictype">{{$fe['semanticTypes'][$feObj->idFrameElement]->name ?? ''}}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>


                {{--                <div id="frameReport">--}}
                {{--                    <div class="flex flex-row align-content-start flex-wrap">--}}
                {{--                        <div class="col-12 sm:col-12 md:col-12 lg:col-7 xl:col-6">--}}
                {{--                            <h1>--}}
                {{--                                <x-element.frame name="{{$frame->name}}"></x-element.frame>--}}
                {{--                            </h1>--}}
                {{--                            <div>--}}
                {{--                                @foreach ($classification as $name => $classes)--}}
                {{--                                    @if(($name == 'rel_framal_domain') || ($name == 'rel_framal_type'))--}}
                {{--                                        @foreach ($classes as $value)--}}
                {{--                                            <div class="ui small label">--}}
                {{--                                                {{$value}}--}}
                {{--                                            </div>--}}
                {{--                                        @endforeach--}}
                {{--                                    @endif--}}
                {{--                                @endforeach--}}
                {{--                            </div>--}}
                {{--                        </div>--}}
                {{--                        <div--}}
                {{--                            class="col-12 sm:col-12 md:col-12 lg:col-5 xl:col-6 flex gap-1 flex-wrap align-items-center justify-content-end">--}}
                {{--                            <div class="ui label wt-tag-id">--}}
                {{--                                {{$classification['id'][0]}}--}}
                {{--                            </div>--}}
                {{--                            <div class="ui label wt-tag-en">--}}
                {{--                                {{$classification['en'][0]}}--}}
                {{--                            </div>--}}
                {{--                            <button--}}
                {{--                                id="btnDownload"--}}
                {{--                                class="ui button mini basic secondary"--}}
                {{--                            ><i class="icon material">download</i>PDF--}}
                {{--                            </button>--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                    <x-card title="Definition">--}}
                {{--                        {!! str_replace('ex>','code>',nl2br($frame->description)) !!}--}}
                {{--                    </x-card>--}}
                {{--                    <x-card title="Frame Elements">--}}
                {{--                        <table class="ui celled striped table">--}}
                {{--                            <thead>--}}
                {{--                            <tr>--}}
                {{--                                <th colspan="4">Core</th>--}}
                {{--                            </tr>--}}
                {{--                            </thead>--}}
                {{--                            <tbody>--}}
                {{--                            @foreach ($fe['core'] as $feObj)--}}
                {{--                                <tr>--}}
                {{--                                    <td class="collapsing">--}}
                {{--                                        <span class="color_{{$feObj->idColor}}">{{$feObj->name}}</span>--}}
                {{--                                    </td>--}}
                {{--                                    <td class="pl-2">{!! $feObj->description !!}</td>--}}
                {{--                                    <td>--}}
                {{--                                        @foreach ($feObj->relations as $relation)--}}
                {{--                                            <b>{{$relation['name']}}:&nbsp;</b>{{$relation['relatedFEName']}}--}}
                {{--                                        @endforeach--}}
                {{--                                    </td>--}}
                {{--                                    <td class="right aligned collapsing">--}}
                {{--                                        {{$fe['semanticTypes'][$feObj->idFrameElement]->name ?? ''}}--}}
                {{--                                    </td>--}}
                {{--                                </tr>--}}
                {{--                            @endforeach--}}
                {{--                            </tbody>--}}
                {{--                        </table>--}}

                {{--                        @if ($fe['core_unexpressed'])--}}
                {{--                            <table class="ui celled striped table">--}}
                {{--                                <thead>--}}
                {{--                                <tr>--}}
                {{--                                    <th colspan="4">Core-Unexpressed</th>--}}
                {{--                                </tr>--}}
                {{--                                </thead>--}}
                {{--                                <tbody>--}}
                {{--                                @foreach ($fe['core_unexpressed'] as $feObj)--}}
                {{--                                    <tr>--}}
                {{--                                        <td class="collapsing">--}}
                {{--                                            <span class="color_{{$feObj->idColor}}">{{$feObj->name}}</span>--}}
                {{--                                        </td>--}}
                {{--                                        <td class="pl-2">{!! $feObj->description !!}</td>--}}
                {{--                                        <td>--}}
                {{--                                            @foreach ($feObj->relations as $relation)--}}
                {{--                                                <b>{{$relation['name']}}:&nbsp;</b>{{$relation['relatedFEName']}}--}}
                {{--                                            @endforeach--}}
                {{--                                        </td>--}}
                {{--                                        <td class="right aligned collapsing">--}}
                {{--                                            {{$fe['semanticTypes'][$feObj->idFrameElement]->name ?? ''}}--}}
                {{--                                        </td>--}}
                {{--                                    </tr>--}}
                {{--                                @endforeach--}}
                {{--                                </tbody>--}}
                {{--                            </table>--}}
                {{--                        @endif--}}
                {{--                        @if ($fecoreset)--}}
                {{--                            <x-card title="FE Core set(s)">--}}
                {{--                                <table id="feCoreSet" class="frameReport__table">--}}
                {{--                                    <tbody>--}}
                {{--                                    <tr>--}}
                {{--                                        <td class="pl-2">{{$fecoreset}}</td>--}}
                {{--                                    </tr>--}}
                {{--                                    </tbody>--}}
                {{--                                </table>--}}
                {{--                            </x-card>--}}
                {{--                        @endif--}}
                {{--                        @if ($fe['peripheral'])--}}
                {{--                            <table class="ui celled striped table">--}}
                {{--                                <thead>--}}
                {{--                                <tr>--}}
                {{--                                    <th colspan="4">Peripheral</th>--}}
                {{--                                </tr>--}}
                {{--                                </thead>--}}
                {{--                                <tbody>--}}
                {{--                                @foreach ($fe['peripheral'] as $feObj)--}}
                {{--                                    <tr>--}}
                {{--                                        <td class="collapsing">--}}
                {{--                                            <span class="color_{{$feObj->idColor}}">{{$feObj->name}}</span>--}}
                {{--                                        </td>--}}
                {{--                                        <td class="pl-2">{!! $feObj->description !!}</td>--}}
                {{--                                        <td>--}}
                {{--                                            @foreach ($feObj->relations as $relation)--}}
                {{--                                                <b>{{$relation['name']}}:&nbsp;</b>{{$relation['relatedFEName']}}--}}
                {{--                                            @endforeach--}}
                {{--                                        </td>--}}
                {{--                                        <td class="right aligned collapsing">--}}
                {{--                                            {{$fe['semanticTypes'][$feObj->idFrameElement]->name ?? ''}}--}}
                {{--                                        </td>--}}
                {{--                                    </tr>--}}
                {{--                                @endforeach--}}
                {{--                                </tbody>--}}
                {{--                            </table>--}}
                {{--                        @endif--}}
                {{--                        @if ($fe['extra_thematic'])--}}
                {{--                            <table class="ui celled striped table">--}}
                {{--                                <thead>--}}
                {{--                                <tr>--}}
                {{--                                    <th colspan="4">Extra-thematic</th>--}}
                {{--                                </tr>--}}
                {{--                                </thead>--}}
                {{--                                <tbody>--}}
                {{--                                @foreach ($fe['extra_thematic'] as $feObj)--}}
                {{--                                    <tr>--}}
                {{--                                        <td class="collapsing">--}}
                {{--                                            <span class="color_{{$feObj->idColor}}">{{$feObj->name}}</span>--}}
                {{--                                        </td>--}}
                {{--                                        <td class="pl-2">{!! $feObj->description !!}</td>--}}
                {{--                                        <td>--}}
                {{--                                            @foreach ($feObj->relations as $relation)--}}
                {{--                                                <b>{{$relation['name']}}:&nbsp;</b>{{$relation['relatedFEName']}}--}}
                {{--                                            @endforeach--}}
                {{--                                        </td>--}}
                {{--                                        <td class="right aligned collapsing">--}}
                {{--                                            {{$fe['semanticTypes'][$feObj->idFrameElement]->name ?? ''}}--}}
                {{--                                        </td>--}}
                {{--                                    </tr>--}}
                {{--                                @endforeach--}}
                {{--                                </tbody>--}}
                {{--                            </table>--}}
                {{--                        @endif--}}
                {{--                    </x-card>--}}
                {{--                    <x-card title="Frame-Frame Relations">--}}
                {{--                        @php($i = 0)--}}
                {{--                        @foreach ($relations as $nameEntry => $relations1)--}}
                {{--                            @php([$entry, $name] = explode('|', $nameEntry))--}}
                {{--                            @php($relId = str_replace(' ', '_', $name))--}}
                {{--                            <x-card-plain--}}
                {{--                                title="<span class='color_{{$entry}}'>{{$name}}</span>">--}}
                {{--                                <div class="flex flex-wrap gap-1">--}}
                {{--                                    @foreach ($relations1 as $idFrame => $relation)--}}
                {{--                                        <button--}}
                {{--                                            id="btnRelation_{{$relId}}_{{$idFrame}}"--}}
                {{--                                            class="ui button basic"--}}
                {{--                                        >--}}
                {{--                                            <a--}}
                {{--                                                href="/report/frame/{{$idFrame}}"--}}
                {{--                                            >--}}
                {{--                                                <x-element.frame name="{{$relation['name']}}"></x-element.frame>--}}
                {{--                                            </a>--}}
                {{--                                        </button>--}}
                {{--                                    @endforeach--}}
                {{--                                </div>--}}
                {{--                            </x-card-plain>--}}
                {{--                        @endforeach--}}
                {{--                    </x-card>--}}
                {{--                    <x-card title="Lexical Units">--}}
                {{--                        @foreach ($lus as $POS => $posLU)--}}
                {{--                            <x-card-plain--}}
                {{--                                title="POS: {{$POS}}"--}}
                {{--                            >--}}
                {{--                                <div class="flex flex-wrap gap-1">--}}
                {{--                                    @foreach ($posLU as $lu)--}}
                {{--                                        <button--}}
                {{--                                            id="btnLU{{$lu->idLU}}"--}}

                {{--                                            class="ui button basic"--}}
                {{--                                        ><a href="/report/lu/{{$lu->idLU}}">{{$lu->name}}</a></button>--}}
                {{--                                    @endforeach--}}
                {{--                                </div>--}}
                {{--                            </x-card-plain>--}}
                {{--                        @endforeach--}}
                {{--                    </x-card>--}}
                {{--                    <x-card title="Visual Units">--}}
                {{--                        @include("Frame.Report.vu")--}}
                {{--                    </x-card>--}}
                {{--                </div>--}}
            </div>
        </main>
        <aside class="app-tools">
            <h3 class="ui header">{{$frame->name}}</h3>
            <div class="ui accordion">
                <div class="title">
                    <i class="dropdown icon"></i>
                    <b>FrameElements</b></div>
                <div class="content">
                    <a class="item d-block" href="#core">Core</a>
                    <a class="item d-block" href="#core-unexpressed">Core Unexpressed</a>
                    <a class="item d-block" href="#peripheral">Peripheral</a>
                    <a class="item d-block" href="#extra-thematic">Extra Thematic</a>
                </div>
            </div>
        </aside>
    </div>
</x-layout::index>

<script>
    $(function() {
        $(".ui.accordion")
            .accordion();
    });


    {{--$("#btnDownload").click(function(e) {--}}
    {{--    const options = {--}}
    {{--        margin: 0.5,--}}
    {{--        filename: '{{$frame->name}}.pdf',--}}
    {{--        image: {--}}
    {{--            type: "jpeg",--}}
    {{--            quality: 500--}}
    {{--        },--}}
    {{--        html2canvas: {--}}
    {{--            scale: 1--}}
    {{--        },--}}
    {{--        jsPDF: {--}}
    {{--            unit: "in",--}}
    {{--            format: "a4",--}}
    {{--            orientation: "portrait"--}}
    {{--        }--}}
    {{--    };--}}

    {{--    e.preventDefault();--}}
    {{--    const element = document.getElementById("frameReport");--}}
    {{--    html2pdf().from(element).set(options).save();--}}
    {{--});--}}
</script>
