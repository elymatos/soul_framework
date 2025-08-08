/**
 * Frame Visualization Component
 * 
 * Provides interactive visualization of frame structures, elements, and relationships
 * using Vis.js network library. Extends the base SoulGraphVisualization for frame-specific
 * layouts and interactions.
 */

class FrameVisualization {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        
        if (!this.container) {
            throw new Error(`Container with ID ${containerId} not found`);
        }
        
        // Default options
        this.options = {
            layout: 'hierarchical',
            interactive: true,
            physics: true,
            stabilization: true,
            showLabels: true,
            ...options
        };
        
        // Network instance
        this.network = null;
        this.nodes = new vis.DataSet([]);
        this.edges = new vis.DataSet([]);
        
        // Event handlers
        this.eventHandlers = new Map();
        
        this.initialize();
    }
    
    initialize() {
        try {
            // Check if vis.js is available
            if (typeof vis === 'undefined') {
                console.warn('Vis.js library not found. Loading from CDN...');
                this.loadVisJS().then(() => this.createNetwork());
                return;
            }
            
            this.createNetwork();
            
        } catch (error) {
            console.error('Failed to initialize FrameVisualization:', error);
            this.showError('Failed to initialize frame visualization');
        }
    }
    
    async loadVisJS() {
        return new Promise((resolve, reject) => {
            if (typeof vis !== 'undefined') {
                resolve();
                return;
            }
            
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/vis-network@9.1.2/standalone/umd/vis-network.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }
    
    createNetwork() {
        const networkOptions = {
            layout: this.getLayoutOptions(),
            physics: this.getPhysicsOptions(),
            nodes: this.getNodeOptions(),
            edges: this.getEdgeOptions(),
            interaction: this.getInteractionOptions(),
            configure: false
        };
        
        const data = {
            nodes: this.nodes,
            edges: this.edges
        };
        
        this.network = new vis.Network(this.container, data, networkOptions);
        this.setupEventListeners();
    }
    
    getLayoutOptions() {
        switch (this.options.layout) {
            case 'hierarchical':
                return {
                    hierarchical: {
                        enabled: true,
                        direction: 'UD',
                        sortMethod: 'directed',
                        levelSeparation: 150,
                        nodeSpacing: 200,
                        treeSpacing: 200
                    }
                };
                
            case 'circular':
                return {
                    randomSeed: 2
                };
                
            case 'force':
                return {
                    randomSeed: 2
                };
                
            default:
                return {
                    randomSeed: 2
                };
        }
    }
    
    getPhysicsOptions() {
        if (!this.options.physics) {
            return { enabled: false };
        }
        
        switch (this.options.layout) {
            case 'hierarchical':
                return {
                    enabled: true,
                    hierarchicalRepulsion: {
                        centralGravity: 0.0,
                        springLength: 100,
                        springConstant: 0.01,
                        nodeDistance: 120,
                        damping: 0.09
                    },
                    maxVelocity: 50,
                    solver: 'hierarchicalRepulsion',
                    timestep: 0.5,
                    stabilization: { iterations: 1000 }
                };
                
            case 'force':
                return {
                    enabled: true,
                    forceAtlas2Based: {
                        gravitationalConstant: -50,
                        centralGravity: 0.01,
                        springConstant: 0.08,
                        springLength: 100,
                        damping: 0.4,
                        avoidOverlap: 0
                    },
                    maxVelocity: 50,
                    solver: 'forceAtlas2Based',
                    timestep: 0.35,
                    stabilization: { iterations: 150 }
                };
                
            default:
                return {
                    enabled: true,
                    stabilization: { iterations: 100 }
                };
        }
    }
    
    getNodeOptions() {
        return {
            borderWidth: 2,
            borderWidthSelected: 3,
            chosen: {
                node: (values, id, selected, hovering) => {
                    values.borderWidth = selected ? 4 : 2;
                    values.color = selected ? '#FF6B6B' : values.color;
                }
            },
            font: {
                size: 14,
                face: 'Arial',
                strokeWidth: 2,
                strokeColor: '#ffffff'
            },
            scaling: {
                min: 10,
                max: 30
            },
            shadow: {
                enabled: true,
                color: 'rgba(0,0,0,0.2)',
                size: 10,
                x: 2,
                y: 2
            }
        };
    }
    
    getEdgeOptions() {
        return {
            arrows: {
                to: {
                    enabled: true,
                    scaleFactor: 1,
                    type: 'arrow'
                }
            },
            color: {
                color: '#848484',
                highlight: '#FF6B6B',
                hover: '#999999'
            },
            font: {
                size: 11,
                strokeWidth: 2,
                strokeColor: '#ffffff',
                align: 'middle'
            },
            smooth: {
                enabled: true,
                type: 'continuous',
                roundness: 0.2
            },
            width: 2,
            widthConstraint: {
                maximum: 5
            }
        };
    }
    
    getInteractionOptions() {
        return {
            hover: true,
            hoverConnectedEdges: true,
            selectConnectedEdges: false,
            multiselect: false,
            dragNodes: this.options.interactive,
            dragView: true,
            zoomView: true
        };
    }
    
    setupEventListeners() {
        if (!this.network) return;
        
        this.network.on('selectNode', (params) => {
            const nodeId = params.nodes[0];
            if (nodeId) {
                const node = this.nodes.get(nodeId);
                this.emit('selectNode', node);
            }
        });
        
        this.network.on('deselectNode', () => {
            this.emit('deselectNode');
        });
        
        this.network.on('doubleClick', (params) => {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = this.nodes.get(nodeId);
                this.emit('doubleClick', node);
            }
        });
        
        this.network.on('hoverNode', (params) => {
            const node = this.nodes.get(params.node);
            this.showTooltip(params.pointer.DOM, node);
        });
        
        this.network.on('blurNode', () => {
            this.hideTooltip();
        });
        
        this.network.on('stabilizationProgress', (params) => {
            this.emit('stabilizationProgress', params);
        });
        
        this.network.on('stabilizationIterationsDone', () => {
            this.emit('stabilizationComplete');
        });
    }
    
    /**
     * Render frame structure visualization
     */
    renderFrameStructure(frameData) {
        try {
            this.clear();
            
            if (!frameData || !frameData.frame) {
                this.showError('Invalid frame data provided');
                return;
            }
            
            const frame = frameData.frame;
            const elements = frameData.elements || [];
            const relationships = frameData.relationships || [];
            
            // Add main frame node
            const frameNode = {
                id: 'frame_' + frame.name,
                label: frame.name,
                title: this.createFrameTooltip(frame),
                color: this.getFrameNodeColor(frame.type),
                shape: 'box',
                size: 25,
                font: { size: 16, bold: true },
                nodeType: 'frame',
                frameData: frame
            };
            
            this.nodes.add(frameNode);
            
            // Add frame element nodes
            elements.forEach((element, index) => {
                const elementNode = {
                    id: 'element_' + element.name,
                    label: element.name,
                    title: this.createElementTooltip(element),
                    color: this.getElementNodeColor(element.type),
                    shape: 'ellipse',
                    size: 15,
                    nodeType: 'element',
                    elementData: element
                };
                
                this.nodes.add(elementNode);
                
                // Add edge from frame to element
                const edge = {
                    id: 'frame_to_' + element.name,
                    from: frameNode.id,
                    to: elementNode.id,
                    label: 'HAS_ELEMENT',
                    color: { color: '#2ECC71' },
                    width: 2
                };
                
                this.edges.add(edge);
            });
            
            // Add relationship nodes and edges
            relationships.forEach((relationship, index) => {
                const relatedNode = {
                    id: 'related_' + relationship.target,
                    label: relationship.target,
                    title: this.createRelationshipTooltip(relationship),
                    color: this.getRelatedNodeColor(),
                    shape: 'diamond',
                    size: 12,
                    nodeType: 'related',
                    relationshipData: relationship
                };
                
                this.nodes.add(relatedNode);
                
                // Add edge for relationship
                const relationshipEdge = {
                    id: 'rel_' + index,
                    from: frameNode.id,
                    to: relatedNode.id,
                    label: relationship.type,
                    color: { color: '#3498DB' },
                    width: 1,
                    dashes: true
                };
                
                this.edges.add(relationshipEdge);
            });
            
            // Fit the network to show all nodes
            this.network.fit({
                animation: {
                    duration: 1000,
                    easingFunction: 'easeInOutQuad'
                }
            });
            
            this.emit('frameRendered', frameData);
            
        } catch (error) {
            console.error('Failed to render frame structure:', error);
            this.showError('Failed to render frame structure: ' + error.message);
        }
    }
    
    /**
     * Highlight frame elements based on matching results
     */
    highlightMatchedElements(matchedElements, missingElements = []) {
        try {
            // Reset all element colors first
            const elementNodes = this.nodes.get().filter(node => node.nodeType === 'element');
            elementNodes.forEach(node => {
                this.nodes.update({
                    id: node.id,
                    color: this.getElementNodeColor(node.elementData?.type)
                });
            });
            
            // Highlight matched elements
            matchedElements.forEach(elementName => {
                const nodeId = 'element_' + elementName;
                const node = this.nodes.get(nodeId);
                if (node) {
                    this.nodes.update({
                        id: nodeId,
                        color: { background: '#2ECC71', border: '#27AE60' }
                    });
                }
            });
            
            // Highlight missing elements
            missingElements.forEach(elementName => {
                const nodeId = 'element_' + elementName;
                const node = this.nodes.get(nodeId);
                if (node) {
                    this.nodes.update({
                        id: nodeId,
                        color: { background: '#E74C3C', border: '#C0392B' }
                    });
                }
            });
            
        } catch (error) {
            console.error('Failed to highlight elements:', error);
        }
    }
    
    /**
     * Get node color based on frame type
     */
    getFrameNodeColor(frameType) {
        const colors = {
            'frame': { background: '#3498DB', border: '#2980B9' },
            'image_schema': { background: '#2ECC71', border: '#27AE60' },
            'meta_schema': { background: '#9B59B6', border: '#8E44AD' },
            'primitive': { background: '#E67E22', border: '#D35400' }
        };
        
        return colors[frameType] || { background: '#95A5A6', border: '#7F8C8D' };
    }
    
    /**
     * Get node color based on element type
     */
    getElementNodeColor(elementType) {
        const colors = {
            'core': { background: '#F39C12', border: '#E67E22' },
            'peripheral': { background: '#F1C40F', border: '#F39C12' },
            'extra_thematic': { background: '#BDC3C7', border: '#95A5A6' }
        };
        
        return colors[elementType] || { background: '#ECF0F1', border: '#BDC3C7' };
    }
    
    /**
     * Get color for related concept nodes
     */
    getRelatedNodeColor() {
        return { background: '#E8F4F8', border: '#3498DB' };
    }
    
    /**
     * Create tooltip content for frame node
     */
    createFrameTooltip(frame) {
        let tooltip = `<div class="frame-tooltip">`;
        tooltip += `<h4>${frame.name}</h4>`;
        tooltip += `<p><strong>Type:</strong> ${frame.type}</p>`;
        if (frame.domain) {
            tooltip += `<p><strong>Domain:</strong> ${frame.domain}</p>`;
        }
        if (frame.description) {
            tooltip += `<p><strong>Description:</strong> ${frame.description}</p>`;
        }
        tooltip += `</div>`;
        
        return tooltip;
    }
    
    /**
     * Create tooltip content for element node
     */
    createElementTooltip(element) {
        let tooltip = `<div class="element-tooltip">`;
        tooltip += `<h4>${element.name}</h4>`;
        tooltip += `<p><strong>Type:</strong> ${element.type || 'core'}</p>`;
        if (element.description) {
            tooltip += `<p><strong>Description:</strong> ${element.description}</p>`;
        }
        if (element.value && typeof element.value === 'object') {
            tooltip += `<p><strong>Value:</strong> ${JSON.stringify(element.value)}</p>`;
        }
        tooltip += `</div>`;
        
        return tooltip;
    }
    
    /**
     * Create tooltip content for relationship node
     */
    createRelationshipTooltip(relationship) {
        let tooltip = `<div class="relationship-tooltip">`;
        tooltip += `<h4>${relationship.target}</h4>`;
        tooltip += `<p><strong>Relationship:</strong> ${relationship.type}</p>`;
        if (relationship.properties && Object.keys(relationship.properties).length > 0) {
            tooltip += `<p><strong>Properties:</strong> ${JSON.stringify(relationship.properties)}</p>`;
        }
        tooltip += `</div>`;
        
        return tooltip;
    }
    
    /**
     * Show tooltip at specific position
     */
    showTooltip(position, node) {
        if (!node || !node.title) return;
        
        let tooltip = document.querySelector('.frame-viz-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.className = 'frame-viz-tooltip ui popup';
            tooltip.style.position = 'absolute';
            tooltip.style.zIndex = '9999';
            tooltip.style.pointerEvents = 'none';
            document.body.appendChild(tooltip);
        }
        
        tooltip.innerHTML = node.title;
        tooltip.style.left = (position.x + 10) + 'px';
        tooltip.style.top = (position.y - 10) + 'px';
        tooltip.style.display = 'block';
    }
    
    /**
     * Hide tooltip
     */
    hideTooltip() {
        const tooltip = document.querySelector('.frame-viz-tooltip');
        if (tooltip) {
            tooltip.style.display = 'none';
        }
    }
    
    /**
     * Clear all nodes and edges
     */
    clear() {
        if (this.nodes) {
            this.nodes.clear();
        }
        if (this.edges) {
            this.edges.clear();
        }
    }
    
    /**
     * Fit network to container
     */
    fit() {
        if (this.network) {
            this.network.fit();
        }
    }
    
    /**
     * Export visualization as image
     */
    exportAsImage(format = 'png') {
        if (!this.network) return;
        
        try {
            const canvas = this.network.getCanvas();
            const dataURL = canvas.toDataURL(`image/${format}`);
            
            // Create download link
            const link = document.createElement('a');
            link.download = `frame_structure.${format}`;
            link.href = dataURL;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
        } catch (error) {
            console.error('Failed to export visualization:', error);
            this.showError('Failed to export visualization: ' + error.message);
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
        
        // Remove tooltip if exists
        const tooltip = document.querySelector('.frame-viz-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
        
        this.eventHandlers.clear();
    }
    
    /**
     * Event handling
     */
    on(event, handler) {
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, []);
        }
        this.eventHandlers.get(event).push(handler);
    }
    
    emit(event, data = null) {
        const handlers = this.eventHandlers.get(event);
        if (handlers) {
            handlers.forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in event handler for ${event}:`, error);
                }
            });
        }
    }
    
    /**
     * Show error message
     */
    showError(message) {
        console.error('FrameVisualization Error:', message);
        
        if (typeof $ !== 'undefined' && $.fn.toast) {
            $("body").toast({
                message: message,
                class: "error",
                showIcon: "exclamation triangle",
                displayTime: 5000,
                position: "top center"
            });
        } else {
            alert(message);
        }
    }
}

// Make FrameVisualization available globally
if (typeof window !== 'undefined') {
    window.FrameVisualization = FrameVisualization;
}

// Export for ES6 modules and CommonJS
export default FrameVisualization;

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FrameVisualization;
}