/**
 * Triplet Fact Network Visualization Component
 * 
 * A specialized visualization component for the triplet-based fact network system
 * using vis-network library. Displays facts as central nodes connected to their
 * constituent concept nodes in a semantically meaningful layout.
 * 
 * Features:
 * - Distinct styling for FactNodes vs Concept nodes
 * - Role-based edge styling (subject, predicate, object, modifiers)
 * - Interactive fact exploration and concept browsing
 * - Network filtering and search capabilities
 * - Export and visualization controls
 */
class TripletNetworkVisualization {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        
        if (!this.container) {
            throw new Error(`Container with id '${containerId}' not found`);
        }

        // Default options
        this.options = {
            apiBaseUrl: '/facts',
            height: '600px',
            physics: true,
            stabilization: true,
            hierarchicalLayout: false,
            showRoleLabels: true,
            highlightTriplets: true,
            ...options
        };

        // Initialize vis-network data structures
        this.nodes = new vis.DataSet([]);
        this.edges = new vis.DataSet([]);
        this.network = null;

        // Track selected facts and concepts
        this.selectedFacts = new Set();
        this.selectedConcepts = new Set();
        this.highlightedTriplet = null;

        // Event callbacks
        this.eventCallbacks = {
            onSelectFact: null,
            onSelectConcept: null,
            onDeselectAll: null,
            onTripletHighlight: null,
            onNetworkStabilized: null
        };

        this.initialize();
    }

    /**
     * Initialize the visualization
     */
    initialize() {
        this.setupContainer();
        this.createNetwork();
        this.setupEventHandlers();
        this.addControlPanel();
    }

    /**
     * Setup the container with proper styling and controls
     */
    setupContainer() {
        this.container.style.height = this.options.height;
        this.container.style.border = '1px solid #e1e1e1';
        this.container.style.borderRadius = '4px';
        this.container.style.position = 'relative';
        this.container.style.backgroundColor = '#fafafa';
        
        // Add loading state
        this.showLoading();
    }

    /**
     * Create the vis-network instance
     */
    createNetwork() {
        const data = {
            nodes: this.nodes,
            edges: this.edges
        };

        const options = this.getNetworkOptions();
        
        this.network = new vis.Network(this.container, data, options);
        this.hideLoading();
    }

    /**
     * Get network configuration options optimized for fact networks
     */
    getNetworkOptions() {
        return {
            nodes: {
                shape: 'box',
                margin: 10,
                font: {
                    size: 14,
                    face: 'Tahoma, Arial, sans-serif',
                    color: '#2c3e50'
                },
                borderWidth: 2,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.2)',
                    size: 5,
                    x: 2,
                    y: 2
                },
                chosen: {
                    node: (values, id, selected, hovering) => {
                        values.shadow = true;
                        values.shadowSize = 10;
                        values.shadowX = 3;
                        values.shadowY = 3;
                        values.borderWidth = 3;
                    }
                }
            },
            edges: {
                width: 2,
                color: { inherit: 'from' },
                smooth: {
                    type: 'continuous',
                    roundness: 0.3
                },
                arrows: {
                    to: {
                        enabled: true,
                        scaleFactor: 0.8,
                        type: 'arrow'
                    }
                },
                font: {
                    size: 11,
                    align: 'middle',
                    background: 'rgba(255,255,255,0.8)',
                    strokeWidth: 2,
                    strokeColor: '#ffffff'
                },
                labelHighlightBold: true
            },
            physics: {
                enabled: this.options.physics,
                stabilization: { 
                    enabled: this.options.stabilization,
                    iterations: 150 
                },
                solver: 'barnesHut',
                barnesHut: {
                    gravitationalConstant: -3000,
                    centralGravity: 0.4,
                    springLength: 120,
                    springConstant: 0.05,
                    damping: 0.12,
                    avoidOverlap: 0.2
                }
            },
            layout: this.options.hierarchicalLayout ? {
                hierarchical: {
                    enabled: true,
                    direction: 'UD',
                    sortMethod: 'directed',
                    shakeTowards: 'roots',
                    levelSeparation: 150,
                    nodeSpacing: 100
                }
            } : {
                improvedLayout: true,
                randomSeed: 42
            },
            interaction: {
                hover: true,
                tooltipDelay: 200,
                hideEdgesOnDrag: false,
                hideNodesOnDrag: false,
                selectConnectedEdges: true,
                multiselect: true
            }
        };
    }

    /**
     * Setup event handlers for network interactions
     */
    setupEventHandlers() {
        // Node selection events
        this.network.on('selectNode', (params) => {
            this.handleNodeSelection(params);
        });

        this.network.on('deselectNode', (params) => {
            this.handleNodeDeselection(params);
        });

        // Node hover events for triplet highlighting
        this.network.on('hoverNode', (params) => {
            this.handleNodeHover(params);
        });

        this.network.on('blurNode', (params) => {
            this.handleNodeBlur(params);
        });

        // Double click for node expansion
        this.network.on('doubleClick', (params) => {
            if (params.nodes.length > 0) {
                this.handleNodeDoubleClick(params.nodes[0]);
            }
        });

        // Context menu
        this.network.on('oncontext', (params) => {
            params.event.preventDefault();
            if (params.nodes.length > 0) {
                this.showContextMenu(params.nodes[0], params.pointer.DOM);
            }
        });

        // Network stabilization
        this.network.on('stabilizationIterationsDone', () => {
            if (this.eventCallbacks.onNetworkStabilized) {
                this.eventCallbacks.onNetworkStabilized();
            }
        });
    }

    /**
     * Add control panel for network manipulation
     */
    addControlPanel() {
        const controlPanel = document.createElement('div');
        controlPanel.className = 'triplet-network-controls';
        controlPanel.style.cssText = `
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            gap: 5px;
        `;

        controlPanel.innerHTML = `
            <button id="toggle-physics" class="ui mini button" title="Toggle Physics">
                <i class="atom icon"></i>
            </button>
            <button id="fit-network" class="ui mini button" title="Fit to Screen">
                <i class="expand arrows alternate icon"></i>
            </button>
            <button id="toggle-labels" class="ui mini button" title="Toggle Role Labels">
                <i class="tags icon"></i>
            </button>
            <button id="export-network" class="ui mini button" title="Export Network">
                <i class="download icon"></i>
            </button>
        `;

        this.container.appendChild(controlPanel);

        // Setup control event handlers
        this.setupControlHandlers(controlPanel);
    }

    /**
     * Setup control panel event handlers
     */
    setupControlHandlers(controlPanel) {
        // Toggle physics
        controlPanel.querySelector('#toggle-physics').addEventListener('click', () => {
            this.options.physics = !this.options.physics;
            this.network.setOptions({ physics: { enabled: this.options.physics } });
        });

        // Fit network to screen
        controlPanel.querySelector('#fit-network').addEventListener('click', () => {
            this.network.fit({
                animation: {
                    duration: 1000,
                    easingFunction: 'easeInOutQuad'
                }
            });
        });

        // Toggle role labels
        controlPanel.querySelector('#toggle-labels').addEventListener('click', () => {
            this.options.showRoleLabels = !this.options.showRoleLabels;
            this.updateEdgeLabels();
        });

        // Export network
        controlPanel.querySelector('#export-network').addEventListener('click', () => {
            this.exportAsImage();
        });
    }

    /**
     * Load fact network data
     */
    async loadFactNetwork(factId, depth = 2) {
        try {
            this.showLoading();
            
            const response = await window.ky.get(`${this.options.apiBaseUrl}/${factId}/network`, {
                searchParams: { depth: depth }
            }).json();

            if (response.success) {
                this.loadNetworkData(response.data);
            } else {
                this.showError('Failed to load network: ' + response.message);
            }
            
        } catch (error) {
            console.error('Failed to load fact network:', error);
            this.showError('Failed to load network data: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Load multiple facts into the network
     */
    async loadMultipleFacts(factIds) {
        try {
            this.showLoading();
            this.clear();

            for (const factId of factIds) {
                await this.addFactToNetwork(factId);
            }

            this.network.fit();
            
        } catch (error) {
            console.error('Failed to load multiple facts:', error);
            this.showError('Failed to load facts: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Add a single fact to the existing network
     */
    async addFactToNetwork(factId) {
        const response = await window.ky.get(`${this.options.apiBaseUrl}/${factId}/network`, {
            searchParams: { depth: 1 }
        }).json();

        if (response.success) {
            const networkData = response.data;
            this.mergeNetworkData(networkData);
        }
    }

    /**
     * Load network data into visualization
     */
    loadNetworkData(networkData) {
        const visNodes = networkData.nodes.map(node => this.transformNodeForVis(node));
        const visEdges = networkData.links.map(edge => this.transformEdgeForVis(edge));

        this.nodes.clear();
        this.edges.clear();
        
        this.nodes.add(visNodes);
        this.edges.add(visEdges);

        // Fit to screen after stabilization
        setTimeout(() => {
            this.network.fit({
                animation: { duration: 1000, easingFunction: 'easeInOutQuad' }
            });
        }, 500);
    }

    /**
     * Merge new network data with existing
     */
    mergeNetworkData(networkData) {
        const existingNodeIds = new Set(this.nodes.getIds());
        const existingEdgeIds = new Set(this.edges.getIds());

        // Add new nodes
        const newNodes = networkData.nodes
            .filter(node => !existingNodeIds.has(node.id))
            .map(node => this.transformNodeForVis(node));

        // Add new edges
        const newEdges = networkData.links
            .filter(edge => {
                const edgeId = `${edge.source}_${edge.target}_${edge.type}`;
                return !existingEdgeIds.has(edgeId);
            })
            .map(edge => this.transformEdgeForVis(edge));

        this.nodes.add(newNodes);
        this.edges.add(newEdges);
    }

    /**
     * Transform node data for vis-network
     */
    transformNodeForVis(node) {
        const isFactNode = node.type === 'FactNode';
        const style = this.getNodeStyle(node, isFactNode);
        
        return {
            id: node.id,
            label: this.formatNodeLabel(node, isFactNode),
            title: this.generateNodeTooltip(node, isFactNode),
            color: style.color,
            shape: style.shape,
            size: style.size,
            font: style.font,
            group: isFactNode ? 'facts' : 'concepts',
            // Store original data
            originalData: node,
            nodeType: isFactNode ? 'fact' : 'concept'
        };
    }

    /**
     * Transform edge data for vis-network
     */
    transformEdgeForVis(edge) {
        const style = this.getEdgeStyle(edge.type, edge.role);
        const edgeId = `${edge.source}_${edge.target}_${edge.type || edge.role}`;
        
        return {
            id: edgeId,
            from: edge.source,
            to: edge.target,
            label: this.options.showRoleLabels ? this.formatRoleLabel(edge.role || edge.type) : '',
            title: this.generateEdgeTooltip(edge),
            color: style.color,
            width: style.width,
            dashes: style.dashes || false,
            // Store original data
            originalData: edge,
            role: edge.role,
            sequence: edge.sequence
        };
    }

    /**
     * Get node styling based on type and properties
     */
    getNodeStyle(node, isFactNode) {
        if (isFactNode) {
            // Fact node styling
            const confidence = node.confidence || 1.0;
            const verified = node.verified || false;
            
            return {
                color: {
                    background: verified ? '#27ae60' : '#3498db',
                    border: verified ? '#1e8449' : '#2980b9',
                    highlight: {
                        background: verified ? '#2ecc71' : '#5dade2',
                        border: verified ? '#27ae60' : '#3498db'
                    }
                },
                shape: 'box',
                size: Math.max(30, confidence * 40),
                font: {
                    size: 13,
                    color: '#ffffff',
                    bold: true
                }
            };
        } else {
            // Concept node styling
            const isPrimitive = node.is_primitive || false;
            const frequency = node.fact_frequency || 0;
            
            return {
                color: {
                    background: isPrimitive ? '#e74c3c' : '#95a5a6',
                    border: isPrimitive ? '#c0392b' : '#7f8c8d',
                    highlight: {
                        background: isPrimitive ? '#ec7063' : '#aab7b8',
                        border: isPrimitive ? '#e74c3c' : '#95a5a6'
                    }
                },
                shape: 'ellipse',
                size: Math.max(20, Math.min(35, 20 + frequency * 2)),
                font: {
                    size: 12,
                    color: '#2c3e50'
                }
            };
        }
    }

    /**
     * Get edge styling based on relationship role
     */
    getEdgeStyle(type, role) {
        const roleStyles = {
            'subject': {
                color: { color: '#e74c3c', highlight: '#c0392b' },
                width: 3,
                dashes: false
            },
            'predicate': {
                color: { color: '#2ecc71', highlight: '#27ae60' },
                width: 4,
                dashes: false
            },
            'object': {
                color: { color: '#3498db', highlight: '#2980b9' },
                width: 3,
                dashes: false
            },
            'modifier': {
                color: { color: '#f39c12', highlight: '#e67e22' },
                width: 2,
                dashes: [5, 5]
            },
            'temporal': {
                color: { color: '#9b59b6', highlight: '#8e44ad' },
                width: 2,
                dashes: [3, 3]
            },
            'spatial': {
                color: { color: '#1abc9c', highlight: '#16a085' },
                width: 2,
                dashes: [7, 3]
            },
            'causal': {
                color: { color: '#e67e22', highlight: '#d35400' },
                width: 3,
                dashes: [10, 2]
            }
        };

        return roleStyles[role] || roleStyles[type] || {
            color: { color: '#95a5a6', highlight: '#7f8c8d' },
            width: 2,
            dashes: false
        };
    }

    /**
     * Format node label for display
     */
    formatNodeLabel(node, isFactNode) {
        if (isFactNode) {
            // Truncate long statements
            const statement = node.statement || node.label || 'Unnamed Fact';
            return statement.length > 30 ? statement.substring(0, 27) + '...' : statement;
        } else {
            return node.name || node.label || 'Unnamed Concept';
        }
    }

    /**
     * Format role labels for edges
     */
    formatRoleLabel(role) {
        if (!role) return '';
        
        const roleLabels = {
            'subject': 'SUBJ',
            'predicate': 'PRED',
            'object': 'OBJ',
            'modifier': 'MOD',
            'temporal': 'TIME',
            'spatial': 'SPACE',
            'causal': 'CAUSE'
        };

        return roleLabels[role] || role.toUpperCase();
    }

    /**
     * Generate node tooltip content
     */
    generateNodeTooltip(node, isFactNode) {
        let content = '<div class="triplet-tooltip">';
        
        if (isFactNode) {
            content += `<h4>Fact: ${node.statement || 'Unnamed'}</h4>`;
            content += `<p><strong>Confidence:</strong> ${(node.confidence || 1.0).toFixed(2)}</p>`;
            content += `<p><strong>Verified:</strong> ${node.verified ? 'Yes' : 'No'}</p>`;
            
            if (node.domain) {
                content += `<p><strong>Domain:</strong> ${node.domain}</p>`;
            }
            
            if (node.fact_type) {
                content += `<p><strong>Type:</strong> ${node.fact_type}</p>`;
            }
        } else {
            content += `<h4>Concept: ${node.name || 'Unnamed'}</h4>`;
            content += `<p><strong>Primitive:</strong> ${node.is_primitive ? 'Yes' : 'No'}</p>`;
            content += `<p><strong>Usage:</strong> ${node.fact_frequency || 0} facts</p>`;
            
            if (node.category) {
                content += `<p><strong>Category:</strong> ${node.category}</p>`;
            }
        }
        
        content += '</div>';
        return content;
    }

    /**
     * Generate edge tooltip content
     */
    generateEdgeTooltip(edge) {
        const role = edge.role || edge.type || 'Unknown';
        let content = `<div class="triplet-tooltip">`;
        content += `<h4>Relationship: ${this.formatRoleLabel(role)}</h4>`;
        content += `<p><strong>Role:</strong> ${role}</p>`;
        
        if (edge.sequence) {
            content += `<p><strong>Sequence:</strong> ${edge.sequence}</p>`;
        }
        
        if (edge.strength) {
            content += `<p><strong>Strength:</strong> ${edge.strength.toFixed(2)}</p>`;
        }
        
        content += '</div>';
        return content;
    }

    /**
     * Handle node selection
     */
    handleNodeSelection(params) {
        const nodeId = params.nodes[0];
        const node = this.nodes.get(nodeId);
        
        if (node.nodeType === 'fact') {
            this.selectedFacts.add(nodeId);
            if (this.eventCallbacks.onSelectFact) {
                this.eventCallbacks.onSelectFact(node.originalData, nodeId);
            }
            
            // Highlight triplet if enabled
            if (this.options.highlightTriplets) {
                this.highlightFactTriplet(nodeId);
            }
        } else {
            this.selectedConcepts.add(nodeId);
            if (this.eventCallbacks.onSelectConcept) {
                this.eventCallbacks.onSelectConcept(node.originalData, nodeId);
            }
        }
    }

    /**
     * Handle node deselection
     */
    handleNodeDeselection(params) {
        this.selectedFacts.clear();
        this.selectedConcepts.clear();
        this.clearTripletHighlight();
        
        if (this.eventCallbacks.onDeselectAll) {
            this.eventCallbacks.onDeselectAll();
        }
    }

    /**
     * Handle node hover
     */
    handleNodeHover(params) {
        const nodeId = params.node;
        const node = this.nodes.get(nodeId);
        
        if (node.nodeType === 'fact' && this.options.highlightTriplets) {
            this.highlightFactTriplet(nodeId, true);
        }
    }

    /**
     * Handle node blur
     */
    handleNodeBlur(params) {
        if (this.highlightedTriplet && !this.selectedFacts.size) {
            this.clearTripletHighlight();
        }
    }

    /**
     * Handle node double click
     */
    handleNodeDoubleClick(nodeId) {
        const node = this.nodes.get(nodeId);
        
        if (node.nodeType === 'fact') {
            // Expand fact network
            this.expandFactNetwork(nodeId);
        } else {
            // Show concept details or related facts
            this.showConceptFacts(nodeId);
        }
    }

    /**
     * Highlight a fact's triplet relationships
     */
    highlightFactTriplet(factId, isHover = false) {
        // Get all edges connected to this fact
        const connectedEdges = this.edges.get().filter(edge => 
            edge.from === factId || edge.to === factId
        );

        // Get core triplet edges (subject, predicate, object)
        const tripletEdges = connectedEdges.filter(edge => 
            ['subject', 'predicate', 'object'].includes(edge.role)
        );

        // Highlight triplet edges and nodes
        const highlightColor = isHover ? '#f1c40f' : '#e74c3c';
        const updateData = [];

        tripletEdges.forEach(edge => {
            updateData.push({
                id: edge.id,
                color: { color: highlightColor, highlight: highlightColor }
            });
        });

        if (updateData.length > 0) {
            this.edges.update(updateData);
            this.highlightedTriplet = factId;
            
            if (this.eventCallbacks.onTripletHighlight) {
                this.eventCallbacks.onTripletHighlight(factId, tripletEdges);
            }
        }
    }

    /**
     * Clear triplet highlighting
     */
    clearTripletHighlight() {
        if (this.highlightedTriplet) {
            const connectedEdges = this.edges.get().filter(edge => 
                edge.from === this.highlightedTriplet || edge.to === this.highlightedTriplet
            );

            const updateData = connectedEdges.map(edge => {
                const style = this.getEdgeStyle(edge.originalData.type, edge.role);
                return {
                    id: edge.id,
                    color: style.color
                };
            });

            this.edges.update(updateData);
            this.highlightedTriplet = null;
        }
    }

    /**
     * Expand fact network by adding related facts
     */
    async expandFactNetwork(factId, depth = 1) {
        try {
            this.showLoading();
            
            const response = await window.ky.get(`${this.options.apiBaseUrl}/${factId}/network`, {
                searchParams: { depth: depth + 1 }
            }).json();

            if (response.success) {
                this.mergeNetworkData(response.data);
            }
            
        } catch (error) {
            console.error('Failed to expand fact network:', error);
            this.showError('Failed to expand network: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Show facts related to a concept
     */
    async showConceptFacts(conceptId) {
        // This would require an API endpoint to get facts involving a concept
        console.log('Show concept facts:', conceptId);
    }

    /**
     * Update edge labels visibility
     */
    updateEdgeLabels() {
        const edges = this.edges.get();
        const updateData = edges.map(edge => ({
            id: edge.id,
            label: this.options.showRoleLabels ? this.formatRoleLabel(edge.role) : ''
        }));
        
        this.edges.update(updateData);
    }

    /**
     * Show context menu
     */
    showContextMenu(nodeId, position) {
        const node = this.nodes.get(nodeId);
        const isFact = node.nodeType === 'fact';
        
        // Remove existing menu
        const existingMenu = document.getElementById('triplet-context-menu');
        if (existingMenu) {
            existingMenu.remove();
        }

        const menu = document.createElement('div');
        menu.id = 'triplet-context-menu';
        menu.className = 'ui vertical menu';
        menu.style.cssText = `
            position: absolute;
            left: ${position.x}px;
            top: ${position.y}px;
            z-index: 10000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        `;

        if (isFact) {
            menu.innerHTML = `
                <div class="item" onclick="tripletNetwork.expandFactNetwork('${nodeId}')">
                    <i class="sitemap icon"></i>
                    Expand Network
                </div>
                <div class="item" onclick="tripletNetwork.focusOnFact('${nodeId}')">
                    <i class="crosshairs icon"></i>
                    Focus on Fact
                </div>
                <div class="item" onclick="window.open('/facts/${nodeId}', '_blank')">
                    <i class="external alternate icon"></i>
                    View Details
                </div>
                <div class="divider"></div>
                <div class="item" onclick="tripletNetwork.removeNode('${nodeId}')">
                    <i class="trash icon"></i>
                    Remove from View
                </div>
            `;
        } else {
            menu.innerHTML = `
                <div class="item" onclick="tripletNetwork.showConceptFacts('${nodeId}')">
                    <i class="list icon"></i>
                    Show Related Facts
                </div>
                <div class="item" onclick="tripletNetwork.focusOnConcept('${nodeId}')">
                    <i class="crosshairs icon"></i>
                    Focus on Concept
                </div>
                <div class="divider"></div>
                <div class="item" onclick="tripletNetwork.removeNode('${nodeId}')">
                    <i class="eye slash icon"></i>
                    Hide Concept
                </div>
            `;
        }

        document.body.appendChild(menu);

        // Remove menu when clicking elsewhere
        setTimeout(() => {
            document.addEventListener('click', function removeMenu() {
                menu.remove();
                document.removeEventListener('click', removeMenu);
            });
        }, 100);
    }

    /**
     * Focus on a specific fact
     */
    focusOnFact(factId) {
        this.network.focus(factId, {
            scale: 1.2,
            animation: { duration: 1000, easingFunction: 'easeInOutQuad' }
        });
        this.network.selectNodes([factId]);
    }

    /**
     * Focus on a specific concept
     */
    focusOnConcept(conceptId) {
        this.network.focus(conceptId, {
            scale: 1.5,
            animation: { duration: 1000, easingFunction: 'easeInOutQuad' }
        });
        this.network.selectNodes([conceptId]);
    }

    /**
     * Remove a node from the visualization
     */
    removeNode(nodeId) {
        this.nodes.remove(nodeId);
        
        // Remove connected edges
        const connectedEdges = this.edges.get().filter(edge => 
            edge.from === nodeId || edge.to === nodeId
        );
        this.edges.remove(connectedEdges.map(edge => edge.id));
    }

    /**
     * Export network as image
     */
    exportAsImage(format = 'png') {
        const canvas = this.network.getCanvas();
        const dataURL = canvas.toDataURL(`image/${format}`);
        
        const link = document.createElement('a');
        link.download = `triplet_network.${format}`;
        link.href = dataURL;
        link.click();
    }

    /**
     * Get network statistics
     */
    getNetworkStatistics() {
        const nodes = this.nodes.get();
        const edges = this.edges.get();
        
        const factNodes = nodes.filter(n => n.nodeType === 'fact');
        const conceptNodes = nodes.filter(n => n.nodeType === 'concept');
        
        return {
            total_nodes: nodes.length,
            fact_nodes: factNodes.length,
            concept_nodes: conceptNodes.length,
            total_edges: edges.length,
            selected_facts: this.selectedFacts.size,
            selected_concepts: this.selectedConcepts.size
        };
    }

    /**
     * Clear the network
     */
    clear() {
        this.nodes.clear();
        this.edges.clear();
        this.selectedFacts.clear();
        this.selectedConcepts.clear();
        this.highlightedTriplet = null;
    }

    /**
     * Show loading state
     */
    showLoading() {
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'triplet-network-loading';
        loadingDiv.className = 'ui active centered inline loader';
        loadingDiv.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        `;
        
        this.container.appendChild(loadingDiv);
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        const loadingDiv = document.getElementById('triplet-network-loading');
        if (loadingDiv) {
            loadingDiv.remove();
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'ui negative message';
        errorDiv.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            max-width: 400px;
        `;
        errorDiv.innerHTML = `
            <i class="close icon" onclick="this.parentElement.remove()"></i>
            <div class="header">Network Error</div>
            <p>${message}</p>
        `;
        
        this.container.appendChild(errorDiv);
    }

    /**
     * Register event callback
     */
    on(eventName, callback) {
        const callbackName = `on${eventName.charAt(0).toUpperCase() + eventName.slice(1)}`;
        if (this.eventCallbacks.hasOwnProperty(callbackName)) {
            this.eventCallbacks[callbackName] = callback;
        }
    }

    /**
     * Destroy the visualization
     */
    destroy() {
        if (this.network) {
            this.network.destroy();
            this.network = null;
        }
        this.container.innerHTML = '';
    }
}

// Export for use in other modules
export default TripletNetworkVisualization;

// Make available globally for legacy usage
window.TripletNetworkVisualization = TripletNetworkVisualization;