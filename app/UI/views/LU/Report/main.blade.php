<x-layout::index>
    <script type="text/javascript" src="/report/lu/script/searchLU"></script>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['/reports','Reports'],['','LUs']]"></x-ui::breadcrumb>
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
                                      hx-post="/report/lu/grid"
                                      hx-target="#gridArea"
                                      hx-swap="innerHTML"
                                      hx-trigger="submit, input delay:500ms from:input[name='lu']">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="field">
                                        <div class="ui left icon input w-full">
                                            <i class="search icon"></i>
                                            <input
                                                type="search"
                                                name="lu"
                                                placeholder="Search LU"
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
                                            <div class="results-count" id="resultsCount">{!! count($lus ?? []) !!}
                                                results
                                            </div>
                                            <div class="search-query-display" id="queryDisplay"></div>
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    @if(count($lus ?? []) == 0)
                                        <div class="empty-state" id="emptyState">
                                            <i class="search icon empty-icon"></i>
                                            <h3 class="empty-title">Ready to search</h3>
                                            <p class="empty-description">
                                                Enter your search term above to find lexical units.
                                            </p>
                                        </div>
                                    @endif

                                    @if(count($lus ?? []) > 0)
                                        <!-- Card View -->
                                        <div class="card-view" x-transition>
                                            <div class="search-results-grid">
                                                @foreach($lus as $lu)
                                                    <div class="ui card fluid result-card"
                                                         data-id="{{$lu->idLU}}"
                                                         @click="window.location.assign(`/report/lu/{{$lu->idLU}}`)"
                                                         tabindex="0"
                                                         @keydown.enter="window.location.assign(`/report/lu/{{$lu->idLU}}`)"
                                                         role="button">
                                                        <div class="content">
                                                            <div class="header">
                                                                <x-ui::element.lu
                                                                    name="{{$lu->name}}"></x-ui::element.lu>
                                                            </div>
                                                            <div class="description">
                                                                {{$lu->frameName}}
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