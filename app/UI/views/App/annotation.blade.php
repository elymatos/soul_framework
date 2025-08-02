@php
    $reports = [
        'annotationfe' => ['FE', '/annotation/fe', 'Corpus annotation for just the FE layer','ui::icon.frame'],
        'annotationfulltext' => ['Full text', '/annotation/fullText', 'Corpus annotation for all layers','ui::icon.frame'],
        'annotationdynamic' => ['Dynamic mode', '/annotation/dynamic', 'Video annotation.', 'ui::icon.frame' ],
        'annotationdeixis' => ['Deixis', '/annotation/deixis', 'Video annotation for deixis.', 'ui::icon.frame'],
        'annotationstaticbox' => ['Static bbox', '/annotation/staticBbox', 'Image annotation.','ui::icon.frame'],
        'annotationstaticevent' => ['Static event', '/annotation/staticEvent', 'Image annotation for eventive frames.','ui::icon.frame'],
    ];
@endphp

<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','Annotation']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="card-grid dense">
                        @foreach($reports as $category => $report)
                            <a
                                class="ui card option-card"
                                data-category="{{$category}}"
                                href="{{$report[1]}}"
                                hx-boost="true"
                            >
                                <div class="content">
                                    <div class="header">
                                        <x-dynamic-component :component="$report[3]" />
                                        {{$report[0]}}
                                    </div>
                                    <div class="description">
                                        {{$report[2]}}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
