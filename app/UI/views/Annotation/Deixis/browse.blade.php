<x-layout::index>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['','Deixis Annotation']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="app-search">
                        <!-- Search Section -->
                        <div class="search-section"
                             x-data="browseSearchComponent()"
                             @htmx:before-request="onSearchStart"
                             @htmx:after-request="onSearchComplete"
                             @htmx:after-swap="onResultsUpdated"
                        >
                            <div class="search-input-group">
                                <form class="ui form"
                                      hx-post="/annotation/fe/tree"
                                      hx-target=".search-results-tree"
                                      hx-swap="innerHTML"
                                      hx-trigger="submit, input delay:500ms from:input[name='frame']">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="three fields">
                                        <div class="field">
                                            <div class="ui left icon input w-full">
                                                <i class="search icon"></i>
                                                <input
                                                    type="search"
                                                    name="corpus"
                                                    placeholder="Search Corpus"
                                                    autocomplete="off"
                                                >
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="ui left icon input w-full">
                                                <i class="search icon"></i>
                                                <input
                                                    type="search"
                                                    name="document"
                                                    placeholder="Search Document"
                                                    autocomplete="off"
                                                >
                                            </div>
                                        </div>
                                        <button type="submit" class="ui medium primary button">
                                            Search
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="gridArea" class="h-full">
                            @fragment("search")
                                <div class="results-container"
                                     class="results-container view-cards"
                                >

                                    @if(count($data) > 0)
                                        <div class="tree-view" x-transition>
                                            <div
                                                class="search-results-tree"
                                                x-data
                                                @tree-item-selected.document="(event) => {
                                                    let type =  event.detail.type;
                                                    let idNode = type + '_' + event.detail.id;
                                                    if (type === 'corpus') {
                                                        event.detail.tree.toggleNodeState(idNode);
                                                    } else if (type === 'document') {
                                                        window.open(`/annotation/deixis/${event.detail.id}`, '_blank');
                                                    }
                                                }"
                                            >

                                                @fragment("tree")
                                                <x-ui::tree :title="$title ?? ''" url="/annotation/deixis/tree"
                                                            :data="$data"></x-ui::tree>
                                                @endfragment
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
