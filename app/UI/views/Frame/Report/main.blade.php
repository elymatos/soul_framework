<x-layout::index>
    <script type="text/javascript" src="/report/frame/script/searchFrame"></script>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['/reports','Reports'],['','Frames']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="app-search">
                        <!-- Search Section -->
                        <div class="search-section"
                             x-data="searchForm()"
                             @htmx:before-request="onSearchStart"
                             @htmx:after-request="onSearchComplete"
                             @htmx:after-swap="onResultsUpdated"
                        >
                            <div class="search-input-group">
                                <form class="ui form"
                                      hx-post="/report/frame/grid"
                                      hx-target="#gridArea"
                                      hx-swap="innerHTML"
                                      hx-trigger="submit, input delay:500ms from:input[name='frame']">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="field">
                                        <div class="ui left icon input w-full">
                                            <i class="search icon"></i>
                                            <input
                                                type="search"
                                                name="frame"
                                                placeholder="Search Frame"
                                                x-model="searchQuery"
                                                autocomplete="off"
                                            >
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="gridArea" class="h-full">
                            @fragment("search")
                                <div class="results-container"
                                     class="results-container view-cards"
                                >

                                    <div class="results-header">
                                        <div class="results-info">
                                            <div class="results-count" id="resultsCount">{!! count($frames) !!}
                                                results
                                            </div>
                                            <div class="search-query-display" id="queryDisplay"></div>
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    @if(count($frames) == 0)
                                        <div class="empty-state" id="emptyState">
                                            <i class="search icon empty-icon"></i>
                                            <h3 class="empty-title">Ready to search</h3>
                                            <p class="empty-description">
                                                Enter your search term above to find frames.
                                            </p>
                                        </div>
                                    @endif

                                    @if(count($frames) > 0)
                                        <!-- Card View -->
                                        <div class="card-view" x-transition>
                                            <div class="search-results-grid">
                                                @foreach($frames as $frame)
                                                    <div class="ui card fluid result-card"
                                                         data-id="{{$frame->idFrame}}"
                                                         @click="window.location.assign(`/report/frame/{{$frame->idFrame}}`)"
                                                         tabindex="0"
                                                         @keydown.enter="window.location.assign(`/report/frame/{{$frame->idFrame}}`)"
                                                         role="button">
                                                        <div class="content">
                                                            <div class="header">
                                                                <x-ui::element.frame
                                                                    name="{{$frame->name}}"></x-ui::element.frame>
                                                            </div>
                                                            <div class="description">
                                                                {{$frame->description}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endfragment
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layout::index>
