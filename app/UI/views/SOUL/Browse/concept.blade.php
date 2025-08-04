<x-layout::index>
    <div class="app-layout">
        @include('layouts.header')
        @include("layouts.sidebar")
        <main class="app-main">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title">
                        <div class="concept-title">
                            {{ $concept['name'] }}
                            @if($concept['is_primitive'] ?? false)
                                <i class="star icon" title="Primitive Concept"></i>
                            @endif
                        </div>
                        <div class="concept-type-badges">
                            @if(!empty($concept['type']))
                                <div class="ui label {{ $this->getTypeClass($concept['type']) }}">
                                    {{ ucfirst($concept['type']) }}
                                </div>
                            @endif
                            @if(!empty($concept['category']) && $concept['category'] !== $concept['type'])
                                <div class="ui basic label">
                                    {{ ucfirst($concept['category']) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="page-subtitle">
                        {{ $concept['description'] ?? 'No description available.' }}
                    </div>
                </div>
            </div>
            
            <div class="page-content">
                <div class="content-container">
                    
                    <!-- Concept Properties -->
                    <h2 class="ui header">Concept Information</h2>
                    <div class="ui card fluid data-card">
                        <div class="content">
                            <div class="ui relaxed divided list">
                                <div class="item">
                                    <div class="header">Name</div>
                                    {{ $concept['name'] }}
                                </div>
                                <div class="item">
                                    <div class="header">Type</div>
                                    {{ ucfirst($concept['type'] ?? 'Unknown') }}
                                </div>
                                <div class="item">
                                    <div class="header">Category</div>
                                    {{ ucfirst($concept['category'] ?? 'Unknown') }}
                                </div>
                                <div class="item">
                                    <div class="header">Is Primitive</div>
                                    {{ ($concept['is_primitive'] ?? false) ? 'Yes' : 'No' }}
                                </div>
                                @if(!empty($concept['created_at']))
                                <div class="item">
                                    <div class="header">Created</div>
                                    {{ $concept['created_at'] }}
                                </div>
                                @endif
                                @if(!empty($concept['updated_at']))
                                <div class="item">
                                    <div class="header">Last Updated</div>
                                    {{ $concept['updated_at'] }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Relationships -->
                    @if(!empty($relationships['outgoing']) || !empty($relationships['incoming']))
                        <h2 class="ui header" id="relationships">
                            <a href="#relationships">Relationships</a>
                        </h2>

                        @if(!empty($relationships['outgoing']))
                            <h3 class="ui header" id="outgoing">
                                <a href="#outgoing">Outgoing Relationships</a>
                            </h3>
                            @foreach($relationships['by_type'] as $relType => $rels)
                                @php($outgoingRels = array_filter($rels, fn($r) => ($r['direction'] ?? 'outgoing') === 'outgoing'))
                                @if(!empty($outgoingRels))
                                    <div class="ui card fluid data-card">
                                        <div class="content">
                                            <div class="header">{{ $relType }}</div>
                                            <div class="description">
                                                <div class="ui relaxed list">
                                                    @foreach($outgoingRels as $rel)
                                                        <div class="item">
                                                            <div class="content">
                                                                <a href="/soul/browse/{{ urlencode($rel['related_concept']) }}" 
                                                                   class="header">{{ $rel['related_concept'] }}</a>
                                                                @if(!empty($rel['description']))
                                                                    <div class="description">{{ $rel['description'] }}</div>
                                                                @endif
                                                                @if(isset($rel['weight']) && $rel['weight'] != 1.0)
                                                                    <div class="meta">Weight: {{ $rel['weight'] }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        @if(!empty($relationships['incoming']))
                            <h3 class="ui header" id="incoming">
                                <a href="#incoming">Incoming Relationships</a>
                            </h3>
                            @foreach($relationships['by_type'] as $relType => $rels)
                                @php($incomingRels = array_filter($rels, fn($r) => ($r['direction'] ?? 'outgoing') === 'incoming'))
                                @if(!empty($incomingRels))
                                    <div class="ui card fluid data-card">
                                        <div class="content">
                                            <div class="header">{{ $relType }}</div>
                                            <div class="description">
                                                <div class="ui relaxed list">
                                                    @foreach($incomingRels as $rel)
                                                        <div class="item">
                                                            <div class="content">
                                                                <a href="/soul/browse/{{ urlencode($rel['related_concept']) }}" 
                                                                   class="header">{{ $rel['related_concept'] }}</a>
                                                                @if(!empty($rel['description']))
                                                                    <div class="description">{{ $rel['description'] }}</div>
                                                                @endif
                                                                @if(isset($rel['weight']) && $rel['weight'] != 1.0)
                                                                    <div class="meta">Weight: {{ $rel['weight'] }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    @endif

                    <!-- Spreading Activation Results -->
                    @if(!empty($activatedConcepts))
                        <h2 class="ui header" id="activation">
                            <a href="#activation">Spreading Activation</a>
                        </h2>
                        <div class="ui card fluid data-card">
                            <div class="content">
                                <div class="header">
                                    Activated Concepts
                                    <div class="ui right floated mini basic label">
                                        {{ count($activatedConcepts) }} concepts
                                    </div>
                                </div>
                                <div class="description">
                                    <div class="ui relaxed divided list">
                                        @foreach($activatedConcepts as $activated)
                                            <div class="item">
                                                <div class="content">
                                                    <a href="/soul/browse/{{ urlencode($activated['name']) }}" 
                                                       class="header">{{ $activated['name'] }}</a>
                                                    <div class="description">
                                                        {{ $activated['description'] ?? 'No description available.' }}
                                                    </div>
                                                    <div class="meta">
                                                        Distance: {{ $activated['distance'] ?? 'Unknown' }}
                                                        @if(isset($activated['activationStrength']))
                                                            | Strength: {{ $activated['activationStrength'] }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Graph Visualization -->
                    @if(!empty($graphData))
                        <h2 class="ui header" id="graph">
                            <a href="#graph">Graph Neighborhood</a>
                        </h2>
                        <div class="ui card fluid data-card">
                            <div class="content">
                                <div class="header">Concept Graph</div>
                                <div class="description">
                                    <div id="conceptGraph" style="height: 400px; border: 1px solid #ddd;">
                                        <!-- Graph visualization would go here -->
                                        <div class="ui placeholder segment">
                                            <div class="ui icon header">
                                                <i class="project diagram icon"></i>
                                                Graph visualization showing {{ count($graphData['nodes'] ?? []) }} nodes 
                                                and {{ count($graphData['links'] ?? []) }} relationships
                                            </div>
                                            <div class="ui primary button" onclick="loadGraphVisualization()">
                                                Load Graph Visualization
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="ui attached bottom tabular menu">
                        <a href="/soul/browse" class="item">
                            <i class="arrow left icon"></i>
                            Back to Browse
                        </a>
                        <a href="/soul/browse/{{ urlencode($conceptName) }}/activation" class="item">
                            <i class="sitemap icon"></i>
                            View Spreading Activation
                        </a>
                        <a href="/soul/browse/{{ urlencode($conceptName) }}/graph" class="item">
                            <i class="project diagram icon"></i>
                            Graph Data (JSON)
                        </a>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Sidebar Navigation -->
        <aside class="app-tools">
            <h3 class="ui header">{{ $conceptName }}</h3>
            <div class="ui accordion">
                <div class="title">
                    <i class="dropdown icon"></i>
                    <b>Sections</b>
                </div>
                <div class="content">
                    <a class="item d-block" href="#relationships">Relationships</a>
                    <a class="item d-block" href="#outgoing">Outgoing</a>
                    <a class="item d-block" href="#incoming">Incoming</a>
                    <a class="item d-block" href="#activation">Spreading Activation</a>
                    <a class="item d-block" href="#graph">Graph</a>
                </div>
            </div>
        </aside>
    </div>

    <style>
        .concept-title {
            display: flex;
            align-items: center;
            gap: 0.5em;
            font-size: 1.5em;
            font-weight: bold;
        }
        
        .concept-type-badges {
            margin-top: 0.5em;
        }
        
        .concept-type-badges .label {
            margin-right: 0.5em;
        }
        
        /* Type-specific colors */
        .soul-type-image-schema { background-color: #2185d0 !important; color: white !important; }
        .soul-type-csp { background-color: #21ba45 !important; color: white !important; }
        .soul-type-meta-schema { background-color: #a333c8 !important; color: white !important; }
        .soul-type-primitive { background-color: #f2711c !important; color: white !important; }
        .soul-type-derived { background-color: #767676 !important; color: white !important; }
    </style>

    <script>
        $(function() {
            $(".ui.accordion").accordion();
        });

        function loadGraphVisualization() {
            const conceptName = '{{ $conceptName }}';
            
            fetch(`/soul/browse/${encodeURIComponent(conceptName)}/graph`)
                .then(response => response.json())
                .then(data => {
                    console.log('Graph data:', data);
                    
                    // Replace placeholder with actual graph info
                    const graphContainer = document.getElementById('conceptGraph');
                    graphContainer.innerHTML = `
                        <div class="ui statistics">
                            <div class="statistic">
                                <div class="value">${data.nodes?.length || 0}</div>
                                <div class="label">Nodes</div>
                            </div>
                            <div class="statistic">
                                <div class="value">${data.links?.length || 0}</div>
                                <div class="label">Links</div>
                            </div>
                        </div>
                        <div class="ui message">
                            <div class="header">Graph Data Available</div>
                            <p>Graph visualization data loaded successfully. 
                               Integration with a graph library like D3.js or vis.js would render the interactive graph here.</p>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error loading graph:', error);
                    const graphContainer = document.getElementById('conceptGraph');
                    graphContainer.innerHTML = `
                        <div class="ui negative message">
                            <div class="header">Error Loading Graph</div>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }
    </script>
</x-layout::index>

@php
function getTypeClass($type) {
    return match($type) {
        'image_schema' => 'soul-type-image-schema',
        'csp' => 'soul-type-csp',
        'meta_schema' => 'soul-type-meta-schema',
        'primitive' => 'soul-type-primitive',
        'derived' => 'soul-type-derived',
        default => 'soul-type-unknown'
    };
}
@endphp