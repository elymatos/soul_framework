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
                              hx-trigger="submit">
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
                              hx-trigger="submit">
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
                            <button class="ui button" onclick="saveGraph()">
                                <i class="save icon"></i>
                                Save Graph
                            </button>
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

            <!-- Notifications are handled by the global messengerComponent.js -->

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
            initializeDropdowns(); // Initialize all dropdowns
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
                    width: 2,
                    color: {
                        color: '#848484',
                        highlight: '#FFA500'
                    },
                    arrows: {
                        to: { enabled: true, scaleFactor: 1 }
                    },
                    font: {
                        color: '#343434',
                        size: 12,
                        strokeWidth: 2,
                        strokeColor: 'white'
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
                if (params.nodes.length > 0) {
                    selectedNodeId = params.nodes[0];
                    // Show relation form automatically when node is selected
                    showRelationFormForNode();
                    // Also show context menu for other options
                    showNodeContextMenu(params.pointer.DOM);
                } else {
                    selectedNodeId = null;
                    cancelRelation();
                }
            });

            graphNetwork.on('oncontext', function(params) {
                params.event.preventDefault();
                if (params.nodes.length > 0) {
                    selectedNodeId = params.nodes[0];
                    showNodeContextMenu(params.pointer.DOM);
                }
            });

            // Update stats when data changes
            graphNodes.on('*', updateStats);
            graphEdges.on('*', updateStats);
        }

        function setupEventHandlers() {
            // Hide menu when clicking elsewhere
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#node-menu')) {
                    hideNodeMenu();
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
            
            // Initialize relation label dropdown
            updateRelationLabelDropdown();
        }

        function applyNodeStyling(nodeData) {
            if (nodeData.type === 'slot') {
                // Slot nodes: half size (10 instead of 20) and light green
                nodeData.size = 10;
                nodeData.color = {
                    background: '#90EE90', // Light green
                    border: '#32CD32',     // Lime green border
                    highlight: {
                        background: '#98FB98', // Pale green when highlighted
                        border: '#228B22'      // Forest green border when highlighted
                    }
                };
            } else {
                // Frame nodes: default styling
                nodeData.size = 20;
                nodeData.color = {
                    background: '#97C2FC', // Default blue
                    border: '#2B7CE9',
                    highlight: {
                        background: '#FFA500',
                        border: '#FF8C00'
                    }
                };
            }
        }

        // Form submission handlers (now use renderNotify system)
        function handleFormSubmitSuccess() {
            // Reset node form after successful submission
            document.getElementById('node-form').reset();
            // Reset dropdown to default value
            if (window.$) {
                $('#node-type-dropdown').dropdown('set selected', 'frame');
            }
        }
        
        function handleRelationSubmitSuccess() {
            // Reset relation form after successful submission
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
                        // Apply styling to loaded nodes
                        const styledNodes = data.nodes.map(node => {
                            // Create a copy to avoid modifying the original
                            const nodeData = { ...node };
                            // Ensure type defaults to 'frame' for backward compatibility
                            if (!nodeData.type) {
                                nodeData.type = 'frame';
                            }
                            applyNodeStyling(nodeData);
                            return nodeData;
                        });
                        graphNodes.add(styledNodes);
                    }
                    if (data.edges) {
                        graphEdges.add(data.edges);
                    }

                    updateNodeDropdown();
                    updateStats();
                    updateRelationLabelDropdown(); // Update dropdown with loaded data

                    // Fit the graph after loading
                    setTimeout(() => {
                        fitGraph();
                    }, 500);
                })
                .catch(() => {
                    showMessage('Failed to load graph data', 'error');
                });
        }

        // Helper function for HTMX save button
        function getGraphData() {
            return {
                nodes: graphNodes.get(),
                edges: graphEdges.get()
            };
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

        function createRelationFromNode() {
            if (selectedNodeId) {
                const selectedNode = graphNodes.get(selectedNodeId);
                // Show the node label in display field, store ID in hidden field
                const relationFromDisplay = document.getElementById('relation-from');
                const relationFromId = document.getElementById('relation-from-id');
                
                if (relationFromDisplay && relationFromId && selectedNode) {
                    relationFromDisplay.value = selectedNode.label || selectedNode.name || selectedNodeId;
                    relationFromId.value = selectedNodeId; // Store actual ID for submission
                }
                document.getElementById('relation-instructions').style.display = 'none';
                document.querySelector('.relation-form').classList.add('active');
                updateNodeDropdown(selectedNodeId);
                updateRelationLabelDropdown(); // Refresh relation label dropdown
            }
            hideNodeMenu();
        }

        function showRelationFormForNode() {
            if (selectedNodeId) {
                console.log('Showing relation form for node:', selectedNodeId);
                const selectedNode = graphNodes.get(selectedNodeId);
                
                const relationFromDisplay = document.getElementById('relation-from');
                const relationFromId = document.getElementById('relation-from-id');
                const relationInstructions = document.getElementById('relation-instructions');
                const relationForm = document.querySelector('.relation-form');
                
                // Show the node label in display field, store ID in hidden field
                if (relationFromDisplay && relationFromId && selectedNode) {
                    relationFromDisplay.value = selectedNode.label || selectedNode.name || selectedNodeId;
                    relationFromId.value = selectedNodeId; // Store actual ID for submission
                }
                if (relationInstructions) relationInstructions.style.display = 'none';
                if (relationForm) {
                    relationForm.classList.add('active');
                    console.log('Relation form should now be visible');
                }
                
                updateNodeDropdown(selectedNodeId);
                updateRelationLabelDropdown(); // Refresh relation label dropdown
            } else {
                console.log('No node selected');
            }
        }

        function deleteSelectedNode() {
            if (selectedNodeId) {
                if (confirm('Are you sure you want to delete this node and all its relations?')) {
                    const formData = new FormData();
                    formData.append('nodeId', selectedNodeId);
                    
                    // Get CSRF token safely
                    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                    if (csrfTokenElement) {
                        formData.append('_token', csrfTokenElement.getAttribute('content'));
                    } else {
                        // Try to get token from existing forms on the page
                        const existingTokenInput = document.querySelector('input[name="_token"]');
                        if (existingTokenInput) {
                            formData.append('_token', existingTokenInput.value);
                        } else {
                            console.error('CSRF token not found');
                        }
                    }

                    fetch('/graph-editor/delete-node', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            graphNodes.remove(selectedNodeId);
                            // Remove related edges (vis.js might handle this automatically)
                            const relatedEdges = graphEdges.get({
                                filter: function(edge) {
                                    return edge.from === selectedNodeId || edge.to === selectedNodeId;
                                }
                            });
                            graphEdges.remove(relatedEdges.map(e => e.id));
                            updateNodeDropdown();
                            updateRelationLabelDropdown(); // Update dropdown since edges were removed
                            showMessage('Node deleted successfully', 'success');
                            
                            // Refresh the network display
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
            
            // Get existing labels
            const existingLabels = getExistingRelationLabels();
            
            // Clear current options
            menu.innerHTML = '';
            
            // Add existing labels as options
            existingLabels.forEach(label => {
                const option = document.createElement('div');
                option.className = 'item';
                option.setAttribute('data-value', label);
                option.textContent = label;
                menu.appendChild(option);
            });
            
            // Initialize or refresh the dropdown
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

        function saveGraph() {
            const graphData = {
                nodes: graphNodes.get(),
                edges: graphEdges.get()
            };
            
            fetch('/graph-editor/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                   document.querySelector('input[name="_token"]')?.value || ''
                },
                body: JSON.stringify(graphData)
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    showMessage('Graph saved successfully', 'success');
                } else {
                    showMessage('Failed to save graph: ' + (response.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                showMessage('Failed to save graph: Network error', 'error');
            });
        }

        // Legacy showMessage function for backward compatibility
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
