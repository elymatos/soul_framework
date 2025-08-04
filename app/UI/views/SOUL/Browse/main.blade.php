<x-layout::index>
    <script type="text/javascript" src="/soul/browse/script/searchConcept"></script>
    <div class="app-layout no-tools">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <x-ui::breadcrumb :sections="[['/','Home'],['/soul','SOUL Framework'],['','Concepts']]"></x-ui::breadcrumb>
            <div class="page-content">
                <div class="content-container">
                    <div class="app-search">
                        <!-- Search Section -->
                        <div class="search-section"
                             x-data="searchConceptForm()"
                             @htmx:before-request="onSearchStart"
                             @htmx:after-request="onSearchComplete"
                             @htmx:after-swap="onResultsUpdated"
                        >
                            <div class="search-input-group">
                                <form class="ui form"
                                      hx-post="/soul/browse/grid"
                                      hx-target="#gridArea"
                                      hx-swap="innerHTML"
                                      hx-trigger="submit, input delay:500ms from:input[name='concept']">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    
                                    <!-- Main Search Input -->
                                    <div class="field">
                                        <div class="ui left icon input w-full">
                                            <i class="search icon"></i>
                                            <input
                                                type="search"
                                                name="concept"
                                                placeholder="Search Concepts"
                                                x-model="searchQuery"
                                                autocomplete="off"
                                            >
                                        </div>
                                    </div>

                                    <!-- Advanced Filters -->
                                    <div class="ui accordion" x-data="{ filtersOpen: false }">
                                        <div class="title" @click="filtersOpen = !filtersOpen">
                                            <i class="dropdown icon" :class="{ 'rotated': filtersOpen }"></i>
                                            Advanced Filters
                                        </div>
                                        <div class="content" :class="{ 'active': filtersOpen }">
                                            <div class="ui grid">
                                                <div class="four wide column">
                                                    <div class="field">
                                                        <label>Type</label>
                                                        <select name="type" class="ui dropdown">
                                                            <option value="">All Types</option>
                                                            <option value="image_schema">Image Schema</option>
                                                            <option value="csp">CSP</option>
                                                            <option value="meta_schema">Meta-schema</option>
                                                            <option value="primitive">Primitive</option>
                                                            <option value="derived">Derived</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="four wide column">
                                                    <div class="field">
                                                        <label>Category</label>
                                                        <select name="category" class="ui dropdown">
                                                            <option value="">All Categories</option>
                                                            <option value="concept">Concept</option>
                                                            <option value="primitive">Primitive</option>
                                                            <option value="meta_schema">Meta-schema</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="four wide column">
                                                    <div class="field">
                                                        <label>Primitives Only</label>
                                                        <div class="ui toggle checkbox">
                                                            <input type="checkbox" name="isPrimitive" value="1">
                                                            <label>Show only primitive concepts</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="four wide column">
                                                    <div class="field">
                                                        <label>Actions</label>
                                                        <div class="ui buttons">
                                                            <button type="button" class="ui button" onclick="clearFilters()">Clear</button>
                                                            <a href="/soul/browse/initialize" class="ui blue button">Initialize Primitives</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Spreading Activation -->
                                            <div class="ui divider"></div>
                                            <div class="field">
                                                <div class="ui toggle checkbox">
                                                    <input type="checkbox" name="spreadingActivation" value="1" x-model="useSpreadingActivation">
                                                    <label>Use Spreading Activation</label>
                                                </div>
                                            </div>
                                            <div x-show="useSpreadingActivation" class="ui grid">
                                                <div class="eight wide column">
                                                    <div class="field">
                                                        <label>Start Concept</label>
                                                        <input type="text" name="startConcept" placeholder="Enter concept name">
                                                    </div>
                                                </div>
                                                <div class="four wide column">
                                                    <div class="field">
                                                        <label>Threshold</label>
                                                        <input type="number" name="activationThreshold" min="0" max="1" step="0.1" value="0.5">
                                                    </div>
                                                </div>
                                                <div class="four wide column">
                                                    <div class="field">
                                                        <label>Max Depth</label>
                                                        <input type="number" name="maxDepth" min="1" max="5" value="2">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="gridArea" class="h-full">
                            @fragment("search")
                                <div class="results-container view-cards">
                                    <div class="results-header">
                                        <div class="results-info">
                                            <div class="results-count" id="resultsCount">{{ count($concepts) }} concepts</div>
                                            <div class="search-query-display" id="queryDisplay">
                                                @if(isset($search) && $search->hasFilters())
                                                    <div class="ui labels">
                                                        @foreach($search->getFilterSummary() as $filter)
                                                            <div class="ui label">{{ $filter }}</div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    @if(count($concepts) == 0)
                                        <div class="empty-state" id="emptyState">
                                            <i class="brain icon empty-icon" style="font-size: 4em; color: #ccc;"></i>
                                            <h3 class="empty-title">
                                                @if(isset($search) && $search->hasFilters())
                                                    No concepts found
                                                @else
                                                    Ready to explore concepts
                                                @endif
                                            </h3>
                                            <p class="empty-description">
                                                @if(isset($search) && $search->hasFilters())
                                                    Try adjusting your search criteria or 
                                                    <a href="/soul/browse/initialize">initialize SOUL primitives</a> first.
                                                @else
                                                    Enter your search term above to find concepts, or
                                                    <a href="/soul/browse/initialize">initialize SOUL primitives</a> to get started.
                                                @endif
                                            </p>
                                        </div>
                                    @endif

                                    @if(count($concepts) > 0)
                                        <!-- Card View -->
                                        <div class="card-view" x-transition>
                                            <div class="search-results-grid">
                                                @foreach($concepts as $concept)
                                                    <div class="ui card fluid result-card {{ $concept['typeClass'] ?? '' }}"
                                                         data-name="{{ $concept['name'] }}"
                                                         @click="window.location.assign(`/soul/browse/{{ urlencode($concept['name']) }}`)"
                                                         tabindex="0"
                                                         @keydown.enter="window.location.assign(`/soul/browse/{{ urlencode($concept['name']) }}`)"
                                                         role="button">
                                                        <div class="content">
                                                            <div class="header">
                                                                <div class="concept-name">
                                                                    {{ $concept['name'] }}
                                                                    @if($concept['isPrimitive'] ?? false)
                                                                        <i class="star icon" title="Primitive Concept"></i>
                                                                    @endif
                                                                </div>
                                                                <div class="concept-badges">
                                                                    @if(!empty($concept['typeDisplay']))
                                                                        <div class="ui small label {{ $concept['typeClass'] ?? '' }}">
                                                                            {{ $concept['typeDisplay'] }}
                                                                        </div>
                                                                    @endif
                                                                    @if(!empty($concept['categoryDisplay']) && $concept['categoryDisplay'] !== $concept['typeDisplay'])
                                                                        <div class="ui small basic label">
                                                                            {{ $concept['categoryDisplay'] }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="description">
                                                                {{ $concept['shortDescription'] ?? $concept['description'] ?? 'No description available.' }}
                                                            </div>
                                                            @if(isset($concept['distance']))
                                                                <div class="meta">
                                                                    <div class="ui tiny label">
                                                                        Distance: {{ $concept['distance'] }}
                                                                    </div>
                                                                </div>
                                                            @endif
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

    <style>
        .soul-type-image-schema { border-left: 4px solid #2185d0; }
        .soul-type-csp { border-left: 4px solid #21ba45; }
        .soul-type-meta-schema { border-left: 4px solid #a333c8; }
        .soul-type-primitive { border-left: 4px solid #f2711c; }
        .soul-type-derived { border-left: 4px solid #767676; }
        
        .concept-name {
            display: flex;
            align-items: center;
            gap: 0.5em;
        }
        
        .concept-badges {
            margin-top: 0.5em;
        }
        
        .concept-badges .label {
            margin-right: 0.5em;
        }
        
        .search-results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1rem;
            padding: 1rem 0;
        }
        
        .result-card {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        
        .results-header {
            padding: 1rem 0;
            border-bottom: 1px solid #ddd;
            margin-bottom: 1rem;
        }
        
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>

    <script>
        function clearFilters() {
            document.querySelector('form').reset();
            document.querySelector('input[name="concept"]').value = '';
            document.querySelector('form').dispatchEvent(new Event('submit'));
        }
        
        $(function() {
            $('.ui.dropdown').dropdown();
            $('.ui.checkbox').checkbox();
            $('.ui.accordion').accordion();
        });
    </script>
</x-layout::index>