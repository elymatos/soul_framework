/**
 * SOUL Framework Graph Visualization Component
 * 
 * A comprehensive JavaScript component for visualizing SOUL Framework
 * conceptual networks using Vis.js Network library.
 * 
 * Features:
 * - Interactive network visualization with pan/zoom
 * - Node styling based on SOUL concept types
 * - Real-time graph updates and manipulation
 * - Tooltip generation with concept details
 * - Event handling for node selection and interaction
 * - Support for spreading activation visualization
 */
class SoulGraphVisualization {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        
        if (!this.container) {
            throw new Error(`Container with id '${containerId}' not found`);
        }

        // Default options
        this.options = {
            apiBaseUrl: '/soul',
            height: '600px',
            physics: true,
            stabilization: true,
            clustering: false,
            ...options
        };

        // Initialize Vis.js data structures
        this.nodes = new vis.DataSet([]);
        this.edges = new vis.DataSet([]);
        this.network = null;

        // Event callbacks
        this.eventCallbacks = {
            onSelectNode: null,
            onDeselectNode: null,
            onHoverNode: null,
            onBlurNode: null,
            onDoubleClick: null
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
    }

    /**
     * Setup the container with proper styling
     */
    setupContainer() {
        this.container.style.height = this.options.height;
        this.container.style.border = '1px solid #e1e1e1';
        this.container.style.borderRadius = '4px';
        this.container.style.position = 'relative';
        
        // Add loading state
        this.showLoading();
    }

    /**
     * Create the Vis.js network
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
     * Get network configuration options
     */
    getNetworkOptions() {
        return {
            nodes: {
                shape: 'dot',
                size: 20,
                font: {
                    size: 14,
                    face: 'Tahoma, Arial, sans-serif',
                    color: '#343434'
                },
                borderWidth: 2,
                shadow: true,
                chosen: {
                    node: (values, id, selected, hovering) => {
                        values.shadow = true;
                        values.shadowSize = 10;
                        values.shadowX = 3;
                        values.shadowY = 3;
                    }
                }
            },
            edges: {
                width: 2,
                color: { inherit: 'from' },
                smooth: {
                    type: 'continuous',
                    roundness: 0.5
                },
                arrows: {
                    to: {
                        enabled: true,
                        scaleFactor: 0.5
                    }
                },
                font: {
                    size: 10,
                    align: 'middle'
                }
            },
            physics: {
                enabled: this.options.physics,
                stabilization: { 
                    enabled: this.options.stabilization,
                    iterations: 100 
                },
                solver: 'barnesHut',
                barnesHut: {
                    gravitationalConstant: -2000,
                    centralGravity: 0.3,
                    springLength: 95,
                    springConstant: 0.04,
                    damping: 0.09
                }
            },
            interaction: {
                hover: true,
                tooltipDelay: 200,
                hideEdgesOnDrag: false,
                hideNodesOnDrag: false
            },
            layout: {
                improvedLayout: true,
                randomSeed: 42
            }
        };
    }

    /**
     * Setup event handlers for network interactions
     */
    setupEventHandlers() {
        // Node selection events
        this.network.on('selectNode', (params) => {
            const nodeId = params.nodes[0];
            const node = this.nodes.get(nodeId);
            if (this.eventCallbacks.onSelectNode) {
                this.eventCallbacks.onSelectNode(node, params);
            }
            this.showNodeTooltip(node, params.pointer.DOM);
        });

        this.network.on('deselectNode', (params) => {
            if (this.eventCallbacks.onDeselectNode) {
                this.eventCallbacks.onDeselectNode(params);
            }
            this.hideTooltip();
        });

        // Node hover events
        this.network.on('hoverNode', (params) => {
            const nodeId = params.node;
            const node = this.nodes.get(nodeId);
            if (this.eventCallbacks.onHoverNode) {
                this.eventCallbacks.onHoverNode(node, params);
            }
            this.showNodeTooltip(node, params.pointer.DOM);
        });

        this.network.on('blurNode', (params) => {
            if (this.eventCallbacks.onBlurNode) {
                this.eventCallbacks.onBlurNode(params);
            }
            this.hideTooltip();
        });

        // Double click for node expansion
        this.network.on('doubleClick', (params) => {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = this.nodes.get(nodeId);
                if (this.eventCallbacks.onDoubleClick) {
                    this.eventCallbacks.onDoubleClick(node, params);
                } else {
                    this.expandNode(node);
                }
            }
        });

        // Context menu for nodes
        this.network.on('oncontext', (params) => {
            params.event.preventDefault();
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = this.nodes.get(nodeId);
                this.showContextMenu(node, params.pointer.DOM);
            }
        });
    }

    /**
     * Load graph data from API endpoint
     */
    async loadConceptGraph(conceptName, depth = 2) {
        try {
            this.showLoading();
            
            const response = await window.ky.get(`${this.options.apiBaseUrl}/graph/visualization-data`, {
                searchParams: {
                    concept: conceptName,
                    depth: depth
                }
            }).json();

            this.loadData(response);
            
        } catch (error) {
            console.error('Failed to load graph data:', error);
            this.showError('Failed to load graph data: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Load data into the visualization
     */
    loadData(graphData) {
        // Transform nodes for Vis.js format
        const visNodes = graphData.nodes.map(node => this.transformNodeForVis(node));
        const visEdges = graphData.links.map(edge => this.transformEdgeForVis(edge));

        // Clear existing data and add new data
        this.nodes.clear();
        this.edges.clear();
        
        this.nodes.add(visNodes);
        this.edges.add(visEdges);

        // Fit to screen
        this.network.fit({
            animation: {
                duration: 1000,
                easingFunction: 'easeInOutQuad'
            }
        });
    }

    /**
     * Transform Neo4j node data to Vis.js format
     */
    transformNodeForVis(node) {
        const style = this.getNodeStyle(node.type, node.isPrimitive, node.labels);
        
        return {
            id: node.id || node.name,
            label: node.name,
            title: this.generateNodeTooltipContent(node),
            color: style.color,
            size: style.size,
            shape: style.shape,
            group: node.group || this.getNodeGroup(node.type, node.isPrimitive),
            // Store original node data
            originalData: node
        };
    }

    /**
     * Transform Neo4j edge data to Vis.js format
     */
    transformEdgeForVis(edge) {
        const style = this.getEdgeStyle(edge.relationshipType);
        
        return {
            id: `${edge.source}_${edge.target}_${edge.relationshipType}`,
            from: edge.source,
            to: edge.target,
            label: this.formatRelationshipLabel(edge.relationshipType),
            title: edge.description || edge.relationshipType,
            color: style.color,
            width: Math.max(1, (edge.weight || 1) * 2),
            // Store original edge data
            originalData: edge
        };
    }

    /**
     * Get node styling based on SOUL concept type
     */
    getNodeStyle(type, isPrimitive, labels = []) {
        const baseStyles = {
            'image_schema': {
                color: { background: '#2185d0', border: '#1e78c7' },
                size: 25,
                shape: 'circle'
            },
            'csp': {
                color: { background: '#21ba45', border: '#16ab39' },
                size: 20,
                shape: 'square'
            },
            'meta_schema': {
                color: { background: '#a333c8', border: '#9627ba' },
                size: 30,
                shape: 'diamond'
            },
            'primitive': {
                color: { background: '#f2711c', border: '#e6640a' },
                size: 22,
                shape: 'dot'
            },
            'derived': {
                color: { background: '#767676', border: '#666666' },
                size: 18,
                shape: 'dot'
            },
            'frame': {
                color: { background: '#e03997', border: '#db2c87' },
                size: 24,
                shape: 'box'
            },
            'PROCEDURAL_AGENT': {
                color: { background: '#00b5ad', border: '#009c95' },
                size: 28,
                shape: 'triangle'
            }
        };

        // Check if this is a procedural agent
        if (labels && labels.includes('PROCEDURAL_AGENT')) {
            return baseStyles['PROCEDURAL_AGENT'];
        }

        // Use type-based styling
        const style = baseStyles[type] || baseStyles['derived'];
        
        // Enhance primitive concepts
        if (isPrimitive) {
            style.color.highlight = { background: '#ffeb3b', border: '#fbc02d' };
            style.size += 5;
        }

        return style;
    }

    /**
     * Get edge styling based on relationship type
     */
    getEdgeStyle(relationshipType) {
        const edgeStyles = {
            'IS_A': {
                color: { color: '#2185d0', highlight: '#1e78c7' },
                dashes: false
            },
            'PART_OF': {
                color: { color: '#21ba45', highlight: '#16ab39' },
                dashes: [5, 5]
            },
            'CAUSES': {
                color: { color: '#db2828', highlight: '#d01919' },
                dashes: false
            },
            'ACTIVATES': {
                color: { color: '#a333c8', highlight: '#9627ba' },
                dashes: [10, 5]
            },
            'SCHEMA_ACTIVATES': {
                color: { color: '#f2711c', highlight: '#e6640a' },
                dashes: [3, 3]
            },
            'HAS_FRAME_ELEMENT': {
                color: { color: '#e03997', highlight: '#db2c87' },
                dashes: false
            }
        };

        return edgeStyles[relationshipType] || {
            color: { color: '#848484', highlight: '#666666' },
            dashes: false
        };
    }

    /**
     * Get node group for clustering
     */
    getNodeGroup(type, isPrimitive) {
        if (isPrimitive) {
            switch(type) {
                case 'image_schema': return 1;
                case 'csp': return 2;
                case 'meta_schema': return 3;
                default: return 4;
            }
        }

        switch(type) {
            case 'derived': return 5;
            case 'primitive': return 6;
            case 'frame': return 7;
            default: return 8;
        }
    }

    /**
     * Generate tooltip content for nodes
     */
    generateNodeTooltipContent(node) {
        let content = `<div class="soul-tooltip">`;
        content += `<h4>${node.name}</h4>`;
        
        if (node.type) {
            content += `<p><strong>Type:</strong> ${node.type}</p>`;
        }
        
        if (node.category) {
            content += `<p><strong>Category:</strong> ${node.category}</p>`;
        }
        
        if (node.isPrimitive) {
            content += `<p><span class="ui small orange label">Primitive</span></p>`;
        }
        
        if (node.description) {
            content += `<p><strong>Description:</strong> ${node.description}</p>`;
        }
        
        if (node.distance !== undefined) {
            content += `<p><strong>Distance:</strong> ${node.distance}</p>`;
        }
        
        content += `</div>`;
        return content;
    }

    /**
     * Format relationship labels for display
     */
    formatRelationshipLabel(relationshipType) {
        return relationshipType.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
    }

    /**
     * Show node tooltip
     */
    showNodeTooltip(node, position) {
        this.hideTooltip(); // Remove any existing tooltip
        
        const tooltip = document.createElement('div');
        tooltip.id = 'soul-node-tooltip';
        tooltip.className = 'ui popup';
        tooltip.innerHTML = this.generateNodeTooltipContent(node.originalData || node);
        tooltip.style.position = 'absolute';
        tooltip.style.left = (position.x + 10) + 'px';
        tooltip.style.top = (position.y - 10) + 'px';
        tooltip.style.zIndex = '9999';
        tooltip.style.maxWidth = '300px';
        
        document.body.appendChild(tooltip);
    }

    /**
     * Hide tooltip
     */
    hideTooltip() {
        const existingTooltip = document.getElementById('soul-node-tooltip');
        if (existingTooltip) {
            existingTooltip.remove();
        }
    }

    /**
     * Show context menu for nodes
     */
    showContextMenu(node, position) {
        // Remove existing context menu
        const existingMenu = document.getElementById('soul-context-menu');
        if (existingMenu) {
            existingMenu.remove();
        }

        const menu = document.createElement('div');
        menu.id = 'soul-context-menu';
        menu.className = 'ui vertical menu';
        menu.style.position = 'absolute';
        menu.style.left = position.x + 'px';
        menu.style.top = position.y + 'px';
        menu.style.zIndex = '10000';
        menu.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';

        menu.innerHTML = `
            <div class="item" onclick="soulGraph.expandNode('${node.id}')">
                <i class="expand arrows alternate icon"></i>
                Expand Node
            </div>
            <div class="item" onclick="soulGraph.focusNode('${node.id}')">
                <i class="crosshairs icon"></i>
                Focus on Node
            </div>
            <div class="item" onclick="soulGraph.hideNode('${node.id}')">
                <i class="eye slash icon"></i>
                Hide Node
            </div>
            <div class="divider"></div>
            <div class="item" onclick="window.location.assign('/soul/browse/${encodeURIComponent(node.label)}')">
                <i class="external alternate icon"></i>
                View Details
            </div>
        `;

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
     * Expand a node to show its neighborhood
     */
    async expandNode(nodeId) {
        try {
            const node = this.nodes.get(nodeId);
            if (!node) return;

            this.showLoading();
            
            const response = await window.ky.get(`${this.options.apiBaseUrl}/graph/visualization-data`, {
                searchParams: {
                    concept: node.label,
                    depth: 1
                }
            }).json();

            // Add new nodes and edges to existing graph
            const newNodes = response.nodes
                .filter(n => !this.nodes.get(n.name))
                .map(n => this.transformNodeForVis(n));
            
            const newEdges = response.links
                .filter(e => !this.edges.get(`${e.source}_${e.target}_${e.relationshipType}`))
                .map(e => this.transformEdgeForVis(e));

            this.nodes.add(newNodes);
            this.edges.add(newEdges);

        } catch (error) {
            console.error('Failed to expand node:', error);
            this.showError('Failed to expand node: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Focus on a specific node
     */
    focusNode(nodeId) {
        this.network.focus(nodeId, {
            scale: 1.5,
            animation: {
                duration: 1000,
                easingFunction: 'easeInOutQuad'
            }
        });
        
        this.network.selectNodes([nodeId]);
    }

    /**
     * Hide a node
     */
    hideNode(nodeId) {
        this.nodes.remove(nodeId);
        
        // Also remove edges connected to this node
        const connectedEdges = this.edges.get().filter(edge => 
            edge.from === nodeId || edge.to === nodeId
        );
        
        this.edges.remove(connectedEdges.map(edge => edge.id));
    }

    /**
     * Show loading state
     */
    showLoading() {
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'soul-graph-loading';
        loadingDiv.className = 'ui active loader';
        loadingDiv.style.position = 'absolute';
        loadingDiv.style.top = '50%';
        loadingDiv.style.left = '50%';
        loadingDiv.style.transform = 'translate(-50%, -50%)';
        loadingDiv.style.zIndex = '1000';
        
        this.container.appendChild(loadingDiv);
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        const loadingDiv = document.getElementById('soul-graph-loading');
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
        errorDiv.style.position = 'absolute';
        errorDiv.style.top = '50%';
        errorDiv.style.left = '50%';
        errorDiv.style.transform = 'translate(-50%, -50%)';
        errorDiv.style.zIndex = '1000';
        errorDiv.innerHTML = `
            <i class="close icon" onclick="this.parentElement.remove()"></i>
            <div class="header">Graph Visualization Error</div>
            <p>${message}</p>
        `;
        
        this.container.appendChild(errorDiv);
    }

    /**
     * Register event callback
     */
    on(eventName, callback) {
        if (this.eventCallbacks.hasOwnProperty(`on${eventName.charAt(0).toUpperCase() + eventName.slice(1)}`)) {
            this.eventCallbacks[`on${eventName.charAt(0).toUpperCase() + eventName.slice(1)}`] = callback;
        }
    }

    /**
     * Update graph layout
     */
    updateLayout(options = {}) {
        const layoutOptions = {
            physics: options.physics !== undefined ? options.physics : this.options.physics,
            ...options
        };
        
        this.network.setOptions({ physics: layoutOptions.physics });
        
        if (options.fit !== false) {
            this.network.fit();
        }
    }

    /**
     * Export graph as image
     */
    exportAsImage(format = 'png') {
        const canvas = this.network.getCanvas();
        const dataURL = canvas.toDataURL(`image/${format}`);
        
        // Create download link
        const link = document.createElement('a');
        link.download = `soul_graph.${format}`;
        link.href = dataURL;
        link.click();
    }

    /**
     * Get network statistics
     */
    getStatistics() {
        return {
            nodeCount: this.nodes.length,
            edgeCount: this.edges.length,
            selectedNodes: this.network.getSelectedNodes(),
            selectedEdges: this.network.getSelectedEdges()
        };
    }

    /**
     * Clear the graph
     */
    clear() {
        this.nodes.clear();
        this.edges.clear();
        this.hideTooltip();
    }

    /**
     * Destroy the visualization
     */
    destroy() {
        if (this.network) {
            this.network.destroy();
            this.network = null;
        }
        this.hideTooltip();
        this.container.innerHTML = '';
    }
}

// Export for use in other modules
export default SoulGraphVisualization;

// Also make available globally for legacy usage
window.SoulGraphVisualization = SoulGraphVisualization;