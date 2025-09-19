<x-layout::index>
    @push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @endpush
    <style>
        .graph-editor-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .editor-header {
            background: #1b1c1d;
            color: white;
            padding: 1rem;
            flex-shrink: 0;
        }
        
        .editor-content {
            flex: 1;
            display: flex;
            min-height: 0;
        }
        
        .sidebar {
            width: 350px;
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
        
        .node-menu {
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
            min-width: 150px;
        }
        
        .node-menu .item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .node-menu .item:hover {
            background: #f5f5f5;
        }
        
        .node-menu .item:last-child {
            border-bottom: none;
        }
        
        .form-section {
            margin-bottom: 1rem;
        }
        
        .stats-panel {
            background: #e8f4f8;
            border: 1px solid #b8daff;
            border-radius: 4px;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .relation-form {
            display: none;
        }
        
        .relation-form.active {
            display: block;
        }
        
        .relation-legend {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0.75rem 1rem;
            margin: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .relation-legend h5 {
            margin: 0 0 0.5rem 0;
            color: #495057;
            font-weight: 600;
        }
        
        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: #495057;
        }
        
        .color-indicator {
            width: 20px;
            height: 3px;
            border-radius: 2px;
            display: inline-block;
        }
        
        .color-indicator.f-slot { background-color: #2B7CE9; }
        .color-indicator.default { background-color: #848484; }
        .color-indicator.qualia-base { background-color: #4CAF50; }
        .color-indicator.q-lu1 { background-color: #9C27B0; }
        .color-indicator.q-lu2 { background-color: #FF9800; }
        
    </style>
    <div class="graph-editor-container">
        <!-- Header -->
        <div class="editor-header">
            <div class="ui container">
                <h2 class="ui header" style="color: white; margin: 0;">
                    <i class="project diagram icon"></i>
                    <div class="content">
                        Graph Editor
                        <div class="sub header" style="color: #ccc;">Create and manage your graph visualizations</div>
                    </div>
                </h2>
            </div>
        </div>

        <!-- Relation Legend -->
        <div class="relation-legend" id="relation-legend" style="display: none;">
            <h5><i class="palette icon"></i>Relation Types</h5>
            <div class="legend-items">
                <span class="legend-item">
                    <div class="color-indicator f-slot"></div>
                    f-slot
                </span>
                <span class="legend-item">
                    <div class="color-indicator default"></div>
                    default
                </span>
                <span class="legend-item">
                    <div class="color-indicator qualia-base"></div>
                    qualia-base
                </span>
                <span class="legend-item">
                    <div class="color-indicator q-lu1"></div>
                    q-lu1
                </span>
                <span class="legend-item">
                    <div class="color-indicator q-lu2"></div>
                    q-lu2
                </span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="editor-content">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="ui padded segment" style="border: none; border-radius: 0;">
                    <!-- Add Node Form -->
                    <div class="form-section">
                        <h4 class="ui header">
                            <i class="plus circle icon"></i>
                            Add Node
                        </h4>
                        <form class="ui form" id="node-form"
                              hx-post="/graph-editor/node"
                              hx-trigger="submit"
                              hx-on::after-request="handleFormSubmitSuccess()">
                            @csrf
                            <div class="field">
                                <label>Node Label</label>
                                <input type="text" name="label" placeholder="Enter node label..." required>
                            </div>
                            <div class="field">
                                <label>Node Type</label>
                                <div class="ui fluid selection dropdown" id="node-type-dropdown">
                                    <input type="hidden" name="type" value="frame">
                                    <i class="dropdown icon"></i>
                                    <div class="default text">Select node type...</div>
                                    <div class="menu">
                                        <div class="item" data-value="frame">
                                            <i class="square icon"></i>
                                            Frame
                                        </div>
                                        <div class="item" data-value="slot">
                                            <i class="circle icon"></i>
                                            Slot
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="ui primary button">
                                <i class="plus icon"></i>
                                Add Node
                            </button>
                        </form>
                    </div>

                    <div class="ui divider"></div>

                    <!-- Add Relation Form -->
                    <div class="form-section">
                        <h4 class="ui header">
                            <i class="linkify icon"></i>
                            Add Relation
                        </h4>
                        <div id="relation-instructions" class="ui info message">
                            <i class="info circle icon"></i>
                            Select a node in the graph first, then use this form to create relations.
                        </div>

                        <form class="ui form relation-form" id="relation-form"
                              hx-post="/graph-editor/relation"
                              hx-trigger="submit"
                              hx-on::after-request="handleRelationSubmitSuccess()">
                            @csrf
                            <input type="hidden" name="from" id="relation-from-id">
                            <div class="field">
                                <label>From Node</label>
                                <input type="text" id="relation-from" readonly>
                            </div>
                            <div class="field">
                                <label>To Node</label>
                                <select name="to" id="relation-to" required>
                                    <option value="">Select target node...</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Relation Label (optional)</label>
                                <div class="ui fluid search selection dropdown" id="relation-label-dropdown">
                                    <input type="hidden" name="label">
                                    <i class="dropdown icon"></i>
                                    <div class="default text">Select existing or type new relation...</div>
                                    <div class="menu" id="relation-label-menu">
                                        <!-- Options will be populated dynamically -->
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="ui green button">
                                <i class="linkify icon"></i>
                                Add Relation
                            </button>
                            <button type="button" class="ui button" onclick="cancelRelation()">
                                Cancel
                            </button>
                        </form>
                    </div>

                    <div class="ui divider"></div>

                    <!-- Graph Stats -->
                    <div class="stats-panel">
                        <h5 class="ui header" style="margin-top: 0;">Graph Statistics</h5>
                        <div class="ui mini statistics">
                            <div class="statistic">
                                <div class="value" id="node-count">0</div>
                                <div class="label">Nodes</div>
                            </div>
                            <div class="statistic">
                                <div class="value" id="edge-count">0</div>
                                <div class="label">Relations</div>
                            </div>
                        </div>
                    </div>

                    <div class="ui divider"></div>

                    <!-- Graph Actions -->
                    <div class="form-section">
                        <h4 class="ui header">
                            <i class="cogs icon"></i>
                            Graph Actions
                        </h4>
                        <div class="ui vertical fluid buttons">
                            <button class="ui button" onclick="fitGraph()">
                                <i class="expand arrows alternate icon"></i>
                                Fit to Screen
                            </button>
                            <button class="ui blue button" onclick="saveGraphToDatabase()">
                                <i class="database icon"></i>
                                Save to Database
                            </button>
                            <button class="ui green button" onclick="exportGraphToJson()">
                                <i class="download icon"></i>
                                Export to JSON
                            </button>
                            <button class="ui purple button" onclick="document.getElementById('json-file-input').click()">
                                <i class="upload icon"></i>
                                Load from JSON
                            </button>
                            <input type="file" id="json-file-input" accept=".json" style="display: none;" onchange="importGraphFromJson(event)">
                            <button class="ui orange button"
                                    hx-get="/graph-editor/reset"
                                    hx-confirm="Are you sure you want to clear the entire graph? This action cannot be undone.">
                                <i class="trash alternate icon"></i>
                                Clear All
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graph Visualization Area -->
            <div class="graph-container">
                <div id="graph-visualization"></div>

                <!-- Node Context Menu -->
                <div id="node-menu" class="node-menu">
                    <div class="item" onclick="createRelationFromNode()">
                        <i class="plus icon"></i>
                        Create Relation
                    </div>
                    <div class="item" onclick="deleteSelectedNode()">
                        <i class="trash icon"></i>
                        Delete Node
                    </div>
                </div>

                <!-- Edge Context Menu -->
                <div id="edge-menu" class="node-menu">
                    <div class="item" onclick="deleteSelectedEdge()">
                        <i class="trash icon"></i>
                        Delete Relation
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Global variables
        let graphNetwork;
        let graphNodes;
        let graphEdges;
        let selectedNodeId = null;
        let selectedEdgeId = null;
        
        // Pre-defined relation types and their colors
        const RELATION_COLORS = {
            'f-slot': '#2B7CE9',
            'default': '#848484',
            'qualia-base': '#4CAF50',
            'q-lu1': '#9C27B0',
            'q-lu2': '#FF9800'
        };
        
        function getRelationColor(label) {
            return RELATION_COLORS[label] || RELATION_COLORS['default'];
        }
        
        // Global graph reload handler
        document.addEventListener('reload-graph-visualization', function(event) {
            console.log('Reloading graph visualization...');
            loadGraphData();
        });

        // Initialize the graph editor when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeGraph();
            loadGraphData();
            setupEventHandlers();
            initializeDropdowns();
        });

        function initializeGraph() {
            // Create data sets
            graphNodes = new vis.DataSet([]);
            graphEdges = new vis.DataSet([]);

            // Create network
            const container = document.getElementById('graph-visualization');
            const data = {
                nodes: graphNodes,
                edges: graphEdges
            };

            const options = {
                nodes: {
                    shape: 'dot',
                    size: 20,
                    font: {
                        size: 14,
                        color: '#000000'
                    },
                    borderWidth: 2,
                    color: {
                        background: '#97C2FC',
                        border: '#2B7CE9',
                        highlight: {
                            background: '#FFA500',
                            border: '#FF8C00'
                        }
                    }
                },
                edges: {
                    width: 3,
                    arrows: {
                        to: { enabled: true, scaleFactor: 1 }
                    },
                    smooth: {
                        type: 'continuous'
                    },
                    font: {
                        size: 0
                    }
                },
                interaction: {
                    dragNodes: true,
                    dragView: true,
                    zoomView: true
                },
                physics: {
                    enabled: true,
                    stabilization: { iterations: 100 }
                }
            };

            graphNetwork = new vis.Network(container, data, options);

            // Prevent infinite resizing by setting a stable size
            graphNetwork.on('stabilizationIterationsDone', function() {
                graphNetwork.stopSimulation();
            });

            // Event listeners
            graphNetwork.on('click', function(params) {
                hideNodeMenu();
                hideEdgeMenu();
                if (params.nodes.length > 0) {
                    selectedNodeId = params.nodes[0];
                    selectedEdgeId = null;
                    showRelationFormForNode();
                    showNodeContextMenu(params.pointer.DOM);
                } else if (params.edges.length > 0) {
                    selectedEdgeId = params.edges[0];
                    selectedNodeId = null;
                    cancelRelation();
                } else {
                    selectedNodeId = null;
                    selectedEdgeId = null;
                    cancelRelation();
                }
            });

            graphNetwork.on('oncontext', function(params) {
                params.event.preventDefault();
                hideNodeMenu();
                hideEdgeMenu();
                if (params.nodes.length > 0) {
                    selectedNodeId = params.nodes[0];
                    selectedEdgeId = null;
                    showNodeContextMenu(params.pointer.DOM);
                } else if (params.edges.length > 0) {
                    selectedEdgeId = params.edges[0];
                    selectedNodeId = null;
                    showEdgeContextMenu(params.pointer.DOM);
                }
            });

            // Update stats when data changes
            graphNodes.on('*', updateStats);
            graphEdges.on('*', updateStats);
        }

        function setupEventHandlers() {
            // Hide menus when clicking elsewhere
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#node-menu')) {
                    hideNodeMenu();
                }
                if (!e.target.closest('#edge-menu')) {
                    hideEdgeMenu();
                }
            });
        }

        function initializeDropdowns() {
            // Initialize node type dropdown
            if (window.$) {
                $('#node-type-dropdown').dropdown({
                    onChange: function(value, text, $selectedItem) {
                        console.log('Node type selected:', value);
                    }
                });
            }
            
            updateRelationLabelDropdown();
        }

        function applyNodeStyling(nodeData) {
            if (nodeData.type === 'slot') {
                nodeData.size = 10;
                nodeData.color = {
                    background: '#90EE90',
                    border: '#32CD32',
                    highlight: {
                        background: '#98FB98',
                        border: '#228B22'
                    }
                };
            } else {
                nodeData.size = 20;
                nodeData.color = {
                    background: '#97C2FC',
                    border: '#2B7CE9',
                    highlight: {
                        background: '#FFA500',
                        border: '#FF8C00'
                    }
                };
            }
        }

        function applyEdgeStyling(edgeData) {
            const relationColor = getRelationColor(edgeData.label);
            edgeData.color = {
                color: relationColor,
                highlight: relationColor,
                hover: relationColor
            };
            // Keep original label in title for tooltips but hide the label completely
            edgeData.title = edgeData.label ? `Relation: ${edgeData.label}` : 'Relation';
            
            // Completely hide the edge label
            edgeData.label = undefined;
            edgeData.font = { size: 0 };
        }

        function updateRelationLegend() {
            const legend = document.getElementById('relation-legend');
            const hasEdges = graphEdges && graphEdges.length > 0;
            legend.style.display = hasEdges ? 'block' : 'none';
        }

        function handleFormSubmitSuccess() {
            document.getElementById('node-form').reset();
            if (window.$) {
                $('#node-type-dropdown').dropdown('set selected', 'frame');
            }
        }
        
        function handleRelationSubmitSuccess() {
            document.getElementById('relation-form').reset();
            cancelRelation();
        }

        function loadGraphData() {
            fetch('/graph-editor/data')
                .then(response => response.json())
                .then(data => {
                    graphNodes.clear();
                    graphEdges.clear();

                    if (data.nodes) {
                        const styledNodes = data.nodes.map(node => {
                            const nodeData = { ...node };
                            if (!nodeData.type) {
                                nodeData.type = 'frame';
                            }
                            applyNodeStyling(nodeData);
                            return nodeData;
                        });
                        graphNodes.add(styledNodes);
                    }
                    if (data.edges) {
                        const styledEdges = data.edges.map(edge => {
                            const edgeData = { ...edge };
                            applyEdgeStyling(edgeData);
                            return edgeData;
                        });
                        graphEdges.add(styledEdges);
                    }

                    updateNodeDropdown();
                    updateStats();
                    updateRelationLabelDropdown();
                    updateRelationLegend();

                    setTimeout(() => {
                        fitGraph();
                    }, 500);
                })
                .catch(() => {
                    showMessage('Failed to load graph data', 'error');
                });
        }

        function showNodeContextMenu(position) {
            const menu = document.getElementById('node-menu');
            menu.style.left = position.x + 'px';
            menu.style.top = position.y + 'px';
            menu.style.display = 'block';
        }

        function hideNodeMenu() {
            const menu = document.getElementById('node-menu');
            menu.style.display = 'none';
        }

        function showEdgeContextMenu(position) {
            const menu = document.getElementById('edge-menu');
            menu.style.left = position.x + 'px';
            menu.style.top = position.y + 'px';
            menu.style.display = 'block';
        }

        function hideEdgeMenu() {
            const menu = document.getElementById('edge-menu');
            menu.style.display = 'none';
        }

        function createRelationFromNode() {
            if (selectedNodeId) {
                const selectedNode = graphNodes.get(selectedNodeId);
                const relationFromDisplay = document.getElementById('relation-from');
                const relationFromId = document.getElementById('relation-from-id');
                
                if (relationFromDisplay && relationFromId && selectedNode) {
                    relationFromDisplay.value = selectedNode.label || selectedNode.name || selectedNodeId;
                    relationFromId.value = selectedNodeId;
                }
                document.getElementById('relation-instructions').style.display = 'none';
                document.querySelector('.relation-form').classList.add('active');
                updateNodeDropdown(selectedNodeId);
                updateRelationLabelDropdown();
                updateRelationLegend();
            }
            hideNodeMenu();
        }

        function showRelationFormForNode() {
            if (selectedNodeId) {
                const selectedNode = graphNodes.get(selectedNodeId);
                
                const relationFromDisplay = document.getElementById('relation-from');
                const relationFromId = document.getElementById('relation-from-id');
                const relationInstructions = document.getElementById('relation-instructions');
                const relationForm = document.querySelector('.relation-form');
                
                if (relationFromDisplay && relationFromId && selectedNode) {
                    relationFromDisplay.value = selectedNode.label || selectedNode.name || selectedNodeId;
                    relationFromId.value = selectedNodeId;
                }
                if (relationInstructions) relationInstructions.style.display = 'none';
                if (relationForm) {
                    relationForm.classList.add('active');
                }
                
                updateNodeDropdown(selectedNodeId);
                updateRelationLabelDropdown();
                updateRelationLegend();
            }
        }

        function deleteSelectedNode() {
            if (selectedNodeId) {
                if (confirm('Are you sure you want to delete this node and all its relations?')) {
                    const formData = new FormData();
                    formData.append('nodeId', selectedNodeId);
                    
                    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                    if (csrfTokenElement) {
                        formData.append('_token', csrfTokenElement.getAttribute('content'));
                    }

                    fetch('/graph-editor/delete-node', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            graphNodes.remove(selectedNodeId);
                            const relatedEdges = graphEdges.get({
                                filter: function(edge) {
                                    return edge.from === selectedNodeId || edge.to === selectedNodeId;
                                }
                            });
                            graphEdges.remove(relatedEdges.map(e => e.id));
                            updateNodeDropdown();
                            updateRelationLabelDropdown();
                            updateRelationLegend();
                            showMessage('Node deleted successfully', 'success');
                            
                            if (graphNetwork) {
                                graphNetwork.redraw();
                                graphNetwork.stabilize();
                                setTimeout(() => {
                                    graphNetwork.stopSimulation();
                                }, 100);
                            }
                        } else {
                            showMessage('Failed to delete node: ' + (response.error || 'Unknown error'), 'error');
                        }
                    })
                    .catch(() => {
                        showMessage('Failed to delete node', 'error');
                    });
                }
            }
            hideNodeMenu();
        }

        function deleteSelectedEdge() {
            if (selectedEdgeId) {
                if (confirm('Are you sure you want to delete this relation?')) {
                    const formData = new FormData();
                    formData.append('edgeId', selectedEdgeId);
                    
                    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                    if (csrfTokenElement) {
                        formData.append('_token', csrfTokenElement.getAttribute('content'));
                    }

                    fetch('/graph-editor/delete-edge', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            graphEdges.remove(selectedEdgeId);
                            updateRelationLabelDropdown();
                            updateRelationLegend();
                            showMessage('Relation deleted successfully', 'success');
                            
                            if (graphNetwork) {
                                graphNetwork.redraw();
                                graphNetwork.stabilize();
                                setTimeout(() => {
                                    graphNetwork.stopSimulation();
                                }, 100);
                            }
                        } else {
                            showMessage('Failed to delete relation: ' + (response.error || 'Unknown error'), 'error');
                        }
                    })
                    .catch(() => {
                        showMessage('Failed to delete relation', 'error');
                    });
                }
            }
            hideEdgeMenu();
        }

        function cancelRelation() {
            document.getElementById('relation-instructions').style.display = 'block';
            document.querySelector('.relation-form').classList.remove('active');
            document.getElementById('relation-form').reset();
        }

        function updateNodeDropdown(excludeId = null) {
            const dropdown = document.getElementById('relation-to');
            dropdown.innerHTML = '<option value="">Select target node...</option>';

            graphNodes.get().forEach(node => {
                if (node.id !== excludeId) {
                    const option = document.createElement('option');
                    option.value = node.id;
                    option.textContent = node.label || node.name;
                    dropdown.appendChild(option);
                }
            });
        }

        function updateStats() {
            document.getElementById('node-count').textContent = graphNodes.length;
            document.getElementById('edge-count').textContent = graphEdges.length;
        }

        function getExistingRelationLabels() {
            const labels = new Set();
            graphEdges.get().forEach(edge => {
                if (edge.label && edge.label.trim() !== '') {
                    labels.add(edge.label.trim());
                }
            });
            return Array.from(labels).sort();
        }

        function updateRelationLabelDropdown() {
            const dropdown = document.getElementById('relation-label-dropdown');
            const menu = document.getElementById('relation-label-menu');
            
            if (!dropdown || !menu) return;
            
            menu.innerHTML = '';
            
            // Add pre-defined relation types first
            const predefinedTypes = Object.keys(RELATION_COLORS);
            predefinedTypes.forEach(label => {
                const option = document.createElement('div');
                option.className = 'item';
                option.setAttribute('data-value', label);
                option.innerHTML = `<div class="color-indicator ${label}"></div> ${label}`;
                menu.appendChild(option);
            });
            
            // Add existing custom labels that aren't in predefined types
            const existingLabels = getExistingRelationLabels();
            existingLabels.forEach(label => {
                if (!predefinedTypes.includes(label)) {
                    const option = document.createElement('div');
                    option.className = 'item';
                    option.setAttribute('data-value', label);
                    option.innerHTML = `<div class="color-indicator default"></div> ${label}`;
                    menu.appendChild(option);
                }
            });
            
            if (window.$) {
                $(dropdown).dropdown('destroy').dropdown({
                    allowAdditions: true,
                    hideAdditions: false,
                    message: {
                        addResult: 'Add <b>{term}</b> as new relation label'
                    },
                    onAdd: function(addedValue, addedText, $addedChoice) {
                        console.log('Added new relation label:', addedValue);
                    }
                });
            }
        }

        function fitGraph() {
            if (graphNetwork) {
                graphNetwork.fit({
                    animation: { duration: 1000, easingFunction: 'easeInOutQuad' }
                });
            }
        }

        function saveGraphToDatabase() {
            const graphData = {
                nodes: graphNodes.get(),
                edges: graphEdges.get()
            };
            
            fetch('/graph-editor/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(graphData)
            })
            .then(response => {
                if (response.status === 204) {
                    const hxTrigger = response.headers.get('HX-Trigger');
                    if (hxTrigger) {
                        try {
                            const triggers = JSON.parse(hxTrigger);
                            if (triggers.notify) {
                                showMessage(triggers.notify.message, triggers.notify.type);
                            }
                        } catch (e) {
                            showMessage('Graph saved to database successfully', 'success');
                        }
                    } else {
                        showMessage('Graph saved to database successfully', 'success');
                    }
                    return;
                }
                
                return response.json().then(data => {
                    if (data.success) {
                        showMessage('Graph saved to database successfully', 'success');
                    } else {
                        showMessage('Failed to save graph: ' + (data.error || 'Unknown error'), 'error');
                    }
                });
            })
            .catch(error => {
                showMessage('Failed to save graph: Network error', 'error');
            });
        }

        function exportGraphToJson() {
            const graphData = {
                nodes: graphNodes.get(),
                edges: graphEdges.get(),
                metadata: {
                    exportedAt: new Date().toISOString(),
                    nodeCount: graphNodes.length,
                    edgeCount: graphEdges.length,
                    soulFrameworkVersion: '1.0'
                }
            };
            
            const dataStr = JSON.stringify(graphData, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            
            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = `soul-graph-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.json`;
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showMessage('Graph exported to JSON file successfully', 'success');
        }

        function importGraphFromJson(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            if (!file.name.endsWith('.json')) {
                showMessage('Please select a JSON file', 'error');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const jsonData = JSON.parse(e.target.result);
                    
                    if (!jsonData.nodes || !Array.isArray(jsonData.nodes)) {
                        throw new Error('Invalid JSON structure: missing or invalid nodes array');
                    }
                    if (!jsonData.edges || !Array.isArray(jsonData.edges)) {
                        throw new Error('Invalid JSON structure: missing or invalid edges array');
                    }
                    
                    const nodeCount = jsonData.nodes.length;
                    const edgeCount = jsonData.edges.length;
                    const confirmMessage = `Import ${nodeCount} nodes and ${edgeCount} edges from JSON file? This will replace the current graph.`;
                    
                    if (confirm(confirmMessage)) {
                        loadGraphFromJsonData(jsonData);
                    }
                } catch (error) {
                    showMessage('Failed to import JSON file: ' + error.message, 'error');
                }
            };
            reader.readAsText(file);
            
            event.target.value = '';
        }

        function loadGraphFromJsonData(jsonData) {
            fetch('/graph-editor/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => {
                if (response.status === 204) {
                    const hxTrigger = response.headers.get('HX-Trigger');
                    if (hxTrigger) {
                        try {
                            const triggers = JSON.parse(hxTrigger);
                            if (triggers.notify) {
                                showMessage(triggers.notify.message, triggers.notify.type);
                                if (triggers.notify.type === 'success') {
                                    loadGraphData();
                                }
                            }
                        } catch (e) {
                            showMessage('Graph imported successfully', 'success');
                            loadGraphData();
                        }
                    } else {
                        showMessage('Graph imported successfully from JSON file', 'success');
                        loadGraphData();
                    }
                    return;
                }
                
                return response.json().then(data => {
                    if (data.success) {
                        loadGraphData();
                        showMessage('Graph imported successfully from JSON file', 'success');
                    } else {
                        showMessage('Failed to import graph: ' + (data.error || 'Unknown error'), 'error');
                    }
                });
            })
            .catch(error => {
                showMessage('Failed to import graph: Network error', 'error');
            });
        }

        function showMessage(message, type) {
            if (window.messenger) {
                messenger.notify(type, message);
            } else {
                console.log(`${type.toUpperCase()}: ${message}`);
            }
        }
    </script>
</x-layout::index>