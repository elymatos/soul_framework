<x-layout::index>
    @push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @endpush
    <style>
        .graph-viewer-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .viewer-header {
            background: #1b1c1d;
            color: white;
            padding: 1rem;
            flex-shrink: 0;
        }

        .viewer-content {
            flex: 1;
            display: flex;
            min-height: 0;
        }

        .metadata-sidebar {
            width: 300px;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .graph-container {
            flex: 1;
            position: relative;
            overflow: hidden;
            min-height: 0;
        }

        #graph-visualization {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        .metadata-section {
            margin-bottom: 1rem;
        }

        .metadata-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 0.5rem;
            border: 2px solid #333;
        }

        .legend-shape {
            width: 20px;
            height: 20px;
            margin-right: 0.5rem;
            display: inline-block;
        }

        .control-buttons {
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 0.5rem;
            background: #e9ecef;
            border-radius: 4px;
        }
    </style>

    <div class="graph-viewer-container">
        <!-- Header -->
        <div class="viewer-header">
            <div class="ui container">
                <h2 class="ui header" style="color: white; margin: 0;">
                    <i class="project diagram icon"></i>
                    <div class="content">
                        Graph Viewer: {{ $filename }}
                        <div class="sub header" style="color: #ccc;">
                            @if(isset($metadata['chapter_title']))
                                Chapter {{ $metadata['chapter'] ?? '' }}: {{ $metadata['chapter_title'] }}
                            @else
                                Interactive graph visualization
                            @endif
                        </div>
                    </div>
                </h2>
            </div>
        </div>

        <!-- Main Content -->
        <div class="viewer-content">
            <!-- Metadata Sidebar -->
            <div class="metadata-sidebar">
                <div class="ui padded segment" style="border: none; border-radius: 0;">

                    <!-- Controls -->
                    <div class="control-buttons">
                        <div class="ui vertical fluid buttons">
                            <button class="ui button" onclick="fitGraph()">
                                <i class="expand arrows alternate icon"></i>
                                Fit to Screen
                            </button>
                            <button class="ui blue button" onclick="togglePhysics()">
                                <i class="bolt icon"></i>
                                Toggle Physics
                            </button>
                            <button class="ui green button" onclick="exportCurrentGraph()">
                                <i class="download icon"></i>
                                Export View
                            </button>
                            <a href="/graph-editor" class="ui orange button">
                                <i class="edit icon"></i>
                                Open in Editor
                            </a>
                            <a href="/graph-viewer" class="ui button">
                                <i class="list icon"></i>
                                Browse Graphs
                            </a>
                        </div>
                    </div>

                    <div class="ui divider"></div>

                    <!-- Graph Statistics -->
                    <div class="metadata-section">
                        <h4 class="ui header">
                            <i class="chart bar icon"></i>
                            Graph Statistics
                        </h4>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="value" id="node-count">0</div>
                                <div class="label">Nodes</div>
                            </div>
                            <div class="stat-item">
                                <div class="value" id="edge-count">0</div>
                                <div class="label">Edges</div>
                            </div>
                        </div>

                        @if(isset($metadata['axiom_count']))
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="value">{{ $metadata['axiom_count'] }}</div>
                                <div class="label">Axioms</div>
                            </div>
                            <div class="stat-item">
                                <div class="value">{{ $metadata['predicate_count'] ?? 0 }}</div>
                                <div class="label">Predicates</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="ui divider"></div>

                    <!-- Legend -->
                    <div class="metadata-section">
                        <h4 class="ui header">
                            <i class="tags icon"></i>
                            Node Types
                        </h4>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #E3F2FD; border-color: #1976D2;"></div>
                            <span>Axioms (boxes)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #4CAF50; border-color: #2E7D32;"></div>
                            <span>High-freq Predicates</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #FF9800; border-color: #F57C00;"></div>
                            <span>Med-freq Predicates</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #FFC107; border-color: #FF8F00;"></div>
                            <span>Low-freq Predicates</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #9C27B0; border-color: #4A148C;"></div>
                            <span>Variables (triangles)</span>
                        </div>
                    </div>

                    <div class="ui divider"></div>

                    <!-- Edge Types -->
                    <div class="metadata-section">
                        <h4 class="ui header">
                            <i class="linkify icon"></i>
                            Edge Types
                        </h4>
                        <div class="ui small list">
                            <div class="item">
                                <div style="border-bottom: 2px solid #333; width: 20px; margin-right: 0.5rem;"></div>
                                <span>Uses Predicate</span>
                            </div>
                            <div class="item">
                                <div style="border-bottom: 2px dashed #666; width: 20px; margin-right: 0.5rem;"></div>
                                <span>Has Variable</span>
                            </div>
                            <div class="item">
                                <div style="border-bottom: 2px dashed #999; width: 20px; margin-right: 0.5rem;"></div>
                                <span>Co-occurs</span>
                            </div>
                        </div>
                    </div>

                    @if(isset($metadata['chapter']))
                    <div class="ui divider"></div>

                    <!-- Chapter Information -->
                    <div class="metadata-section">
                        <h4 class="ui header">
                            <i class="book icon"></i>
                            Chapter Info
                        </h4>
                        <div class="metadata-card">
                            <div class="ui padded segment">
                                <p><strong>Chapter:</strong> {{ $metadata['chapter'] }}</p>
                                @if(isset($metadata['chapter_title']))
                                <p><strong>Title:</strong> {{ $metadata['chapter_title'] }}</p>
                                @endif
                                @if(isset($metadata['filename']))
                                <p><strong>Filename:</strong> {{ $metadata['filename'] }}</p>
                                @endif
                                @if(isset($metadata['generated_at']))
                                <p><strong>Generated:</strong> {{ date('Y-m-d H:i', strtotime($metadata['generated_at'])) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- Graph Visualization Area -->
            <div class="graph-container">
                <div id="graph-visualization"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Global variables
        let graphNetwork;
        let graphNodes;
        let graphEdges;
        let physicsEnabled = true;

        // Graph data from the server
        const graphData = @json($graphData);

        // Initialize the graph viewer when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeGraph();
            loadGraphData();
        });

        function initializeGraph() {
            // Create data sets
            graphNodes = new vis.DataSet(graphData.nodes || []);
            graphEdges = new vis.DataSet(graphData.edges || []);

            // Create network
            const container = document.getElementById('graph-visualization');
            const data = {
                nodes: graphNodes,
                edges: graphEdges
            };

            const options = {
                nodes: {
                    font: {
                        size: 12,
                        color: '#000000'
                    },
                    borderWidth: 2,
                    shadow: {
                        enabled: true,
                        color: 'rgba(0,0,0,0.2)',
                        size: 3,
                        x: 2,
                        y: 2
                    }
                },
                edges: {
                    font: {
                        color: '#343434',
                        size: 10,
                        strokeWidth: 2,
                        strokeColor: 'white'
                    },
                    arrows: {
                        to: { enabled: true, scaleFactor: 0.8 }
                    },
                    smooth: {
                        enabled: true,
                        type: 'dynamic',
                        roundness: 0.2
                    }
                },
                interaction: {
                    dragNodes: true,
                    dragView: true,
                    zoomView: true,
                    hover: true,
                    tooltipDelay: 200
                },
                physics: {
                    enabled: true,
                    stabilization: {
                        iterations: 200,
                        fit: true
                    },
                    solver: 'forceAtlas2Based',
                    forceAtlas2Based: {
                        gravitationalConstant: -50,
                        centralGravity: 0.01,
                        springLength: 100,
                        springConstant: 0.08
                    }
                },
                layout: {
                    improvedLayout: true,
                    randomSeed: 42
                }
            };

            graphNetwork = new vis.Network(container, data, options);

            // Event listeners
            graphNetwork.on('click', function(params) {
                if (params.nodes.length > 0) {
                    const nodeId = params.nodes[0];
                    const node = graphNodes.get(nodeId);
                    console.log('Selected node:', node);
                }
            });

            graphNetwork.on('doubleClick', function(params) {
                if (params.nodes.length > 0) {
                    fitToNode(params.nodes[0]);
                }
            });

            // Update stats when data changes
            updateStats();

            // Fit the graph after loading
            setTimeout(() => {
                fitGraph();
            }, 1000);
        }

        function loadGraphData() {
            // Data is already loaded from server, just update the display
            updateStats();
        }

        function updateStats() {
            document.getElementById('node-count').textContent = graphNodes.length;
            document.getElementById('edge-count').textContent = graphEdges.length;
        }

        function fitGraph() {
            if (graphNetwork) {
                graphNetwork.fit({
                    animation: { duration: 1000, easingFunction: 'easeInOutQuad' }
                });
            }
        }

        function fitToNode(nodeId) {
            if (graphNetwork) {
                graphNetwork.focus(nodeId, {
                    scale: 1.5,
                    animation: { duration: 1000, easingFunction: 'easeInOutQuad' }
                });
            }
        }

        function togglePhysics() {
            physicsEnabled = !physicsEnabled;
            if (graphNetwork) {
                graphNetwork.setOptions({ physics: { enabled: physicsEnabled } });

                // Update button text
                const button = event.target.closest('button');
                if (button) {
                    const icon = button.querySelector('i');
                    const text = button.childNodes[button.childNodes.length - 1];
                    if (physicsEnabled) {
                        text.textContent = ' Toggle Physics';
                        icon.className = 'bolt icon';
                    } else {
                        text.textContent = ' Enable Physics';
                        icon.className = 'pause icon';
                    }
                }
            }
        }

        function exportCurrentGraph() {
            const exportData = {
                nodes: graphNodes.get(),
                edges: graphEdges.get(),
                metadata: {
                    filename: '{{ $filename }}',
                    exportedAt: new Date().toISOString(),
                    exportType: 'graph_viewer',
                    nodeCount: graphNodes.length,
                    edgeCount: graphEdges.length
                }
            };

            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });

            // Create download link
            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = `exported-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.json`;

            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showMessage('Graph exported successfully', 'success');
        }

        // Message function for user feedback
        function showMessage(message, type) {
            // Use the global messenger component for consistency
            if (window.messenger) {
                messenger.notify(type, message);
            } else {
                console.log(`${type.toUpperCase()}: ${message}`);
            }
        }
    </script>
</x-layout::index>