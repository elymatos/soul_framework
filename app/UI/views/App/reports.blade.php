@php
    $reports = [
        'reportframe' => ['Frame', '/report/frame', 'List of all frames and its structure.','ui::icon.frame'],
        'reportlu' => ['LU', '/report/lu', 'List of lexical and visual Lexical Units','ui::icon.lu'],
    //    'networkstructure' => ['Network', '/network', 'MASTER', []],
        'cxnreport' => ['Constructions', '/report/cxn', 'List of all constructions and its structure.', 'ui::icon.construction' ],
        'reporttqr' => ['TQR', '/report/qualia', 'Structure of Ternary Qualia Relarion (TQR).', 'ui::icon.qualia'],
        'reportst' => ['SemanticType', '/report/semanticType', 'List of Semantic Types and its hierarchy.','ui::icon.semantictype'],
        'reportc5' => ['MoCCA', '/report/c5', 'List of all Comparative Concepts (CC) of MoCCA Project.','ui::icon.concept'],
    ];
@endphp

<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','Reports']]"></x-ui::breadcrumb>
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
