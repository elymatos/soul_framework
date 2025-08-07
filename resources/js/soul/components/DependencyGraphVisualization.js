/**
 * SOUL Framework - Dependency Graph Visualization Component
 * 
 * Extends the base SoulGraphVisualization to provide specialized dependency graph
 * visualization for YAML knowledge files. Shows hierarchical concept dependencies,
 * procedural agent relationships, and file-based groupings.
 * 
 * @requires d3.js
 * @requires SoulGraphVisualization (base class)
 */

class DependencyGraphVisualization {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        
        if (!this.container) {
            throw new Error(`Container with ID '${containerId}' not found`);
        }
        
        // Configuration options
        this.options = {
            width: options.width || 800,
            height: options.height || 600,
            nodeRadius: {
                concept: 8,
                frame: 12,
                image_schema: 10,
                agent: 14,
                primitive: 6
            },
            colors: {
                concept: '#2185d0',
                frame: '#21ba45',
                image_schema: '#f2711c',
                agent: '#a333c8',
                primitive: '#767676',
                fileGroup: {
                    primitives: '#6435c9',
                    frames: '#00b5ad',
                    domains: '#f2711c',
                    agents: '#e03997'
                }
            },
            forces: {
                linkDistance: 80,
                linkStrength: 0.7,
                chargeStrength: -300,
                centerStrength: 0.1
            },
            animation: {
                duration: 750,
                easing: d3.easeQuadInOut
            },
            ...options
        };
        
        // State
        this.data = { nodes: [], links: [] };
        this.filteredData = { nodes: [], links: [] };
        this.selectedNode = null;
        this.highlightedNodes = new Set();
        this.circularDependencies = [];
        this.fileGroups = {};
        
        // D3 components
        this.svg = null;
        this.simulation = null;
        this.zoom = null;
        
        // DOM elements
        this.nodeGroup = null;
        this.linkGroup = null;
        this.labelGroup = null;
        
        this.initialize();
    }
    
    initialize() {
        this.createSVG();
        this.setupZoom();
        this.createGroups();
        this.setupSimulation();
        this.createArrowMarkers();
        
        // Resize handler
        window.addEventListener('resize', this.handleResize.bind(this));
    }
    
    createSVG() {
        // Clear existing content
        this.container.innerHTML = '';
        
        this.svg = d3.select(this.container)
            .append('svg')
            .attr('width', '100%')
            .attr('height', '100%')
            .attr('viewBox', `0 0 ${this.options.width} ${this.options.height}`)
            .style('background-color', '#fafafa');
    }
    
    setupZoom() {
        this.zoom = d3.zoom()
            .scaleExtent([0.1, 4])
            .on('zoom', (event) => {
                this.svg.select('.main-group')
                    .attr('transform', event.transform);
            });
        
        this.svg.call(this.zoom);
    }
    
    createGroups() {
        const mainGroup = this.svg.append('g').attr('class', 'main-group');
        
        // Create layers in proper order (back to front)
        this.linkGroup = mainGroup.append('g').attr('class', 'links');
        this.nodeGroup = mainGroup.append('g').attr('class', 'nodes');
        this.labelGroup = mainGroup.append('g').attr('class', 'labels');
    }
    
    setupSimulation() {
        this.simulation = d3.forceSimulation()
            .force('link', d3.forceLink().id(d => d.id).distance(this.options.forces.linkDistance))
            .force('charge', d3.forceManyBody().strength(this.options.forces.chargeStrength))
            .force('center', d3.forceCenter(this.options.width / 2, this.options.height / 2))
            .force('collision', d3.forceCollide().radius(d => this.getNodeRadius(d) + 5))
            .on('tick', this.tick.bind(this));
    }
    
    createArrowMarkers() {
        const defs = this.svg.append('defs');
        
        // Define arrow markers for different relationship types
        const relationshipTypes = ['is_a', 'part_of', 'causes', 'activates', 'schema_activates'];
        
        relationshipTypes.forEach(type => {
            defs.append('marker')
                .attr('id', `arrowhead-${type}`)
                .attr('viewBox', '0 -5 10 10')
                .attr('refX', 15)
                .attr('refY', 0)
                .attr('markerWidth', 6)
                .attr('markerHeight', 6)
                .attr('orient', 'auto')
                .append('path')
                .attr('d', 'M0,-5L10,0L0,5')
                .attr('fill', this.getRelationshipColor(type));
        });
        
        // Special marker for circular dependencies
        defs.append('marker')
            .attr('id', 'arrowhead-circular')
            .attr('viewBox', '0 -5 10 10')
            .attr('refX', 15)
            .attr('refY', 0)
            .attr('markerWidth', 8)
            .attr('markerHeight', 8)
            .attr('orient', 'auto')
            .append('path')
            .attr('d', 'M0,-5L10,0L0,5')
            .attr('fill', '#db2828');
    }
    
    loadData(graphData) {
        this.data = {
            nodes: graphData.nodes || [],
            links: graphData.links || []
        };
        
        this.fileGroups = graphData.fileGroups || {};
        this.circularDependencies = graphData.circularDependencies || [];
        
        // Process data
        this.preprocessData();
        
        // Apply initial filter
        this.applyFilter('all');
        
        // Update visualization
        this.update();
        
        // Auto-fit to content
        this.fitToContent();
    }
    
    preprocessData() {
        // Add computed properties to nodes
        this.data.nodes.forEach(node => {
            node.radius = this.getNodeRadius(node);
            node.color = this.getNodeColor(node);
            node.hasAgents = this.hasProceduralAgents(node);
        });
        
        // Add computed properties to links
        this.data.links.forEach(link => {
            link.color = this.getRelationshipColor(link.type);
            link.isCircular = this.isCircularDependency(link);
        });
    }
    
    hasProceduralAgents(node) {
        // Check if this concept has associated procedural agents
        return this.data.nodes.some(n => 
            n.type === 'agent' && 
            (n.activates === node.name || n.relatedConcepts?.includes(node.name))
        );
    }
    
    isCircularDependency(link) {
        return this.circularDependencies.some(cycle => 
            cycle.includes(link.source.id || link.source) && 
            cycle.includes(link.target.id || link.target)
        );
    }
    
    applyFilter(filterType, fileGroup = null) {
        let filteredNodes = [...this.data.nodes];
        let filteredLinks = [...this.data.links];
        
        // Apply type filter
        switch (filterType) {
            case 'frames':
                filteredNodes = filteredNodes.filter(n => n.type === 'frame');
                break;
            case 'image_schema':
                filteredNodes = filteredNodes.filter(n => n.type === 'image_schema');
                break;
            case 'primitives':
                filteredNodes = filteredNodes.filter(n => n.primitive === true);
                break;
            case 'agents':
                filteredNodes = filteredNodes.filter(n => n.hasAgents === true);
                break;
            case 'all':
            default:
                // No filtering
                break;
        }
        
        // Apply file group filter
        if (fileGroup) {
            filteredNodes = filteredNodes.filter(n => n.fileGroup === fileGroup);
        }
        
        // Filter links to only include those between remaining nodes
        const nodeIds = new Set(filteredNodes.map(n => n.id));
        filteredLinks = filteredLinks.filter(l => 
            nodeIds.has(l.source.id || l.source) && 
            nodeIds.has(l.target.id || l.target)
        );
        
        this.filteredData = { nodes: filteredNodes, links: filteredLinks };
        this.update();
    }
    
    update() {
        this.updateLinks();
        this.updateNodes();
        this.updateLabels();
        
        // Update simulation
        this.simulation.nodes(this.filteredData.nodes);
        this.simulation.force('link').links(this.filteredData.links);
        this.simulation.alpha(1).restart();
    }
    
    updateLinks() {
        const links = this.linkGroup.selectAll('.link')
            .data(this.filteredData.links, d => `${d.source.id || d.source}-${d.target.id || d.target}`);
        
        // Remove old links
        links.exit()
            .transition()
            .duration(this.options.animation.duration)
            .style('opacity', 0)
            .remove();
        
        // Add new links
        const linkEnter = links.enter()
            .append('line')
            .attr('class', 'link')
            .style('opacity', 0);
        
        // Update all links
        const linkUpdate = linkEnter.merge(links);
        
        linkUpdate
            .transition()
            .duration(this.options.animation.duration)
            .style('opacity', 1)
            .attr('stroke', d => d.isCircular ? '#db2828' : d.color)
            .attr('stroke-width', d => d.isCircular ? 3 : 2)
            .attr('stroke-dasharray', d => this.getLinkDashArray(d.type))
            .attr('marker-end', d => `url(#arrowhead-${d.isCircular ? 'circular' : d.type})`)
            .attr('class', d => `link link-${d.type} ${d.isCircular ? 'circular-dependency' : ''}`);
        
        // Add hover effects
        linkUpdate
            .on('mouseenter', this.handleLinkHover.bind(this))
            .on('mouseleave', this.handleLinkLeave.bind(this));
    }
    
    updateNodes() {
        const nodes = this.nodeGroup.selectAll('.node')
            .data(this.filteredData.nodes, d => d.id);
        
        // Remove old nodes
        nodes.exit()
            .transition()
            .duration(this.options.animation.duration)
            .attr('r', 0)
            .style('opacity', 0)
            .remove();
        
        // Add new nodes
        const nodeEnter = nodes.enter()
            .append('circle')
            .attr('class', 'node')
            .attr('r', 0)
            .style('opacity', 0)
            .call(this.drag());
        
        // Update all nodes
        const nodeUpdate = nodeEnter.merge(nodes);
        
        nodeUpdate
            .transition()
            .duration(this.options.animation.duration)
            .attr('r', d => d.radius)
            .style('opacity', 1)
            .attr('fill', d => d.color)
            .attr('stroke', '#fff')
            .attr('stroke-width', 2)
            .attr('class', d => `node node-${d.type} ${d.primitive ? 'primitive' : ''}`);
        
        // Add interaction handlers
        nodeUpdate
            .on('click', this.handleNodeClick.bind(this))
            .on('dblclick', this.handleNodeDoubleClick.bind(this))
            .on('mouseenter', this.handleNodeHover.bind(this))
            .on('mouseleave', this.handleNodeLeave.bind(this));
    }
    
    updateLabels() {
        const labels = this.labelGroup.selectAll('.label')
            .data(this.filteredData.nodes, d => d.id);
        
        // Remove old labels
        labels.exit()
            .transition()
            .duration(this.options.animation.duration)
            .style('opacity', 0)
            .remove();
        
        // Add new labels
        const labelEnter = labels.enter()
            .append('text')
            .attr('class', 'label')
            .style('opacity', 0);
        
        // Update all labels
        const labelUpdate = labelEnter.merge(labels);
        
        labelUpdate
            .transition()
            .duration(this.options.animation.duration)
            .style('opacity', 1)
            .text(d => d.name)
            .attr('text-anchor', 'middle')
            .attr('dy', '.35em')
            .attr('font-family', 'Lato, Arial, sans-serif')
            .attr('font-size', '12px')
            .attr('fill', '#333')
            .attr('pointer-events', 'none')
            .style('user-select', 'none');
    }
    
    tick() {
        // Update link positions
        this.linkGroup.selectAll('.link')
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);
        
        // Update node positions
        this.nodeGroup.selectAll('.node')
            .attr('cx', d => d.x)
            .attr('cy', d => d.y);
        
        // Update label positions
        this.labelGroup.selectAll('.label')
            .attr('x', d => d.x)
            .attr('y', d => d.y);
    }
    
    // Event Handlers
    
    handleNodeClick(event, node) {
        event.stopPropagation();
        
        if (this.selectedNode === node) {
            this.clearSelection();
        } else {
            this.selectNode(node);
        }
    }
    
    handleNodeDoubleClick(event, node) {
        event.stopPropagation();
        
        // Emit event for parent to handle (e.g., open file in editor)
        this.container.dispatchEvent(new CustomEvent('node-double-click', {
            detail: { node }
        }));
    }
    
    handleNodeHover(event, node) {
        this.highlightConnectedNodes(node);
    }
    
    handleNodeLeave(event, node) {
        this.clearHighlights();
    }
    
    handleLinkHover(event, link) {
        // Highlight the link and connected nodes
        this.highlightLink(link);
    }
    
    handleLinkLeave(event, link) {
        this.clearHighlights();
    }
    
    // Selection and Highlighting
    
    selectNode(node) {
        this.selectedNode = node;
        this.updateNodeSelection();
        
        // Emit selection event
        this.container.dispatchEvent(new CustomEvent('node-selected', {
            detail: { node }
        }));
    }
    
    clearSelection() {
        this.selectedNode = null;
        this.updateNodeSelection();
        
        // Emit deselection event
        this.container.dispatchEvent(new CustomEvent('node-deselected'));
    }
    
    updateNodeSelection() {
        this.nodeGroup.selectAll('.node')
            .classed('selected', d => d === this.selectedNode)
            .attr('stroke', d => d === this.selectedNode ? '#ff6b35' : '#fff')
            .attr('stroke-width', d => d === this.selectedNode ? 3 : 2);
        
        this.labelGroup.selectAll('.label')
            .classed('selected', d => d === this.selectedNode)
            .style('font-weight', d => d === this.selectedNode ? 'bold' : 'normal')
            .attr('fill', d => d === this.selectedNode ? '#ff6b35' : '#333');
    }
    
    highlightConnectedNodes(node) {
        const connected = new Set([node.id]);
        
        // Find all connected nodes
        this.filteredData.links.forEach(link => {
            const sourceId = link.source.id || link.source;
            const targetId = link.target.id || link.target;
            
            if (sourceId === node.id) {
                connected.add(targetId);
            } else if (targetId === node.id) {
                connected.add(sourceId);
            }
        });
        
        this.highlightedNodes = connected;
        this.updateHighlights();
    }
    
    highlightLink(link) {
        const sourceId = link.source.id || link.source;
        const targetId = link.target.id || link.target;
        
        this.highlightedNodes = new Set([sourceId, targetId]);
        this.updateHighlights();
    }
    
    clearHighlights() {
        this.highlightedNodes.clear();
        this.updateHighlights();
    }
    
    updateHighlights() {
        this.nodeGroup.selectAll('.node')
            .style('opacity', d => 
                this.highlightedNodes.size === 0 || this.highlightedNodes.has(d.id) ? 1 : 0.3
            );
        
        this.linkGroup.selectAll('.link')
            .style('opacity', d => {
                if (this.highlightedNodes.size === 0) return 1;
                
                const sourceId = d.source.id || d.source;
                const targetId = d.target.id || d.target;
                
                return this.highlightedNodes.has(sourceId) && this.highlightedNodes.has(targetId) ? 1 : 0.1;
            });
        
        this.labelGroup.selectAll('.label')
            .style('opacity', d => 
                this.highlightedNodes.size === 0 || this.highlightedNodes.has(d.id) ? 1 : 0.3
            );
    }
    
    // Utility Methods
    
    getNodeRadius(node) {
        const baseRadius = this.options.nodeRadius[node.type] || this.options.nodeRadius.concept;
        return node.primitive ? baseRadius - 2 : baseRadius;
    }
    
    getNodeColor(node) {
        if (node.fileGroup && this.options.colors.fileGroup[node.fileGroup]) {
            return this.options.colors.fileGroup[node.fileGroup];
        }
        
        return this.options.colors[node.type] || this.options.colors.concept;
    }
    
    getRelationshipColor(type) {
        const colors = {
            'is_a': '#2185d0',
            'part_of': '#21ba45',
            'causes': '#e03997',
            'activates': '#f2711c',
            'schema_activates': '#a333c8'
        };
        
        return colors[type.toLowerCase()] || '#999';
    }
    
    getLinkDashArray(type) {
        const patterns = {
            'is_a': 'none',
            'part_of': '5,5',
            'causes': '10,5',
            'activates': '2,2',
            'schema_activates': '8,4,2,4'
        };
        
        return patterns[type.toLowerCase()] || 'none';
    }
    
    // Layout Methods
    
    setLayoutType(layoutType) {
        switch (layoutType) {
            case 'hierarchical':
                this.applyHierarchicalLayout();
                break;
            case 'circular':
                this.applyCircularLayout();
                break;
            case 'radial':
                this.applyRadialLayout();
                break;
            case 'force':
            default:
                this.applyForceLayout();
                break;
        }
    }
    
    applyHierarchicalLayout() {
        this.simulation
            .force('y', d3.forceY(d => this.getHierarchicalY(d)).strength(0.1))
            .force('x', d3.forceX(this.options.width / 2).strength(0.05))
            .alpha(1)
            .restart();
    }
    
    applyCircularLayout() {
        this.simulation
            .force('y', null)
            .force('x', null)
            .force('radial', d3.forceRadial(200, this.options.width / 2, this.options.height / 2))
            .alpha(1)
            .restart();
    }
    
    applyRadialLayout() {
        // Find the most connected node as center
        const centerNode = this.findCentralNode();
        
        if (centerNode) {
            this.simulation
                .force('y', null)
                .force('x', null)
                .force('radial', d3.forceRadial(
                    d => d === centerNode ? 0 : 150,
                    this.options.width / 2,
                    this.options.height / 2
                ))
                .alpha(1)
                .restart();
        }
    }
    
    applyForceLayout() {
        this.simulation
            .force('y', null)
            .force('x', null)
            .force('radial', null)
            .alpha(1)
            .restart();
    }
    
    getHierarchicalY(node) {
        // Position nodes based on their type hierarchy
        const hierarchy = {
            'primitive': 100,
            'image_schema': 200,
            'concept': 300,
            'frame': 400,
            'agent': 500
        };
        
        return hierarchy[node.type] || 300;
    }
    
    findCentralNode() {
        // Find node with most connections
        const connectionCounts = {};
        
        this.filteredData.links.forEach(link => {
            const sourceId = link.source.id || link.source;
            const targetId = link.target.id || link.target;
            
            connectionCounts[sourceId] = (connectionCounts[sourceId] || 0) + 1;
            connectionCounts[targetId] = (connectionCounts[targetId] || 0) + 1;
        });
        
        let maxConnections = 0;
        let centralNodeId = null;
        
        Object.entries(connectionCounts).forEach(([nodeId, count]) => {
            if (count > maxConnections) {
                maxConnections = count;
                centralNodeId = nodeId;
            }
        });
        
        return this.filteredData.nodes.find(n => n.id === centralNodeId);
    }
    
    // Interaction Methods
    
    drag() {
        return d3.drag()
            .on('start', this.dragStarted.bind(this))
            .on('drag', this.dragged.bind(this))
            .on('end', this.dragEnded.bind(this));
    }
    
    dragStarted(event, d) {
        if (!event.active) {
            this.simulation.alphaTarget(0.3).restart();
        }
        
        d.fx = d.x;
        d.fy = d.y;
    }
    
    dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }
    
    dragEnded(event, d) {
        if (!event.active) {
            this.simulation.alphaTarget(0);
        }
        
        d.fx = null;
        d.fy = null;
    }
    
    // View Management
    
    fitToContent() {
        if (this.filteredData.nodes.length === 0) return;
        
        const bounds = this.getBounds();
        const padding = 50;
        
        const scale = Math.min(
            this.options.width / (bounds.width + padding),
            this.options.height / (bounds.height + padding),
            1 // Don't zoom in beyond 100%
        );
        
        const centerX = this.options.width / 2;
        const centerY = this.options.height / 2;
        
        const translateX = centerX - (bounds.centerX * scale);
        const translateY = centerY - (bounds.centerY * scale);
        
        this.svg.transition()
            .duration(this.options.animation.duration)
            .call(
                this.zoom.transform,
                d3.zoomIdentity.translate(translateX, translateY).scale(scale)
            );
    }
    
    getBounds() {
        const nodes = this.filteredData.nodes;
        
        if (nodes.length === 0) {
            return { 
                width: 0, 
                height: 0, 
                centerX: this.options.width / 2, 
                centerY: this.options.height / 2 
            };
        }
        
        const xs = nodes.map(d => d.x).filter(x => x != null);
        const ys = nodes.map(d => d.y).filter(y => y != null);
        
        if (xs.length === 0 || ys.length === 0) {
            return { 
                width: 0, 
                height: 0, 
                centerX: this.options.width / 2, 
                centerY: this.options.height / 2 
            };
        }
        
        const minX = Math.min(...xs);
        const maxX = Math.max(...xs);
        const minY = Math.min(...ys);
        const maxY = Math.max(...ys);
        
        return {
            width: maxX - minX,
            height: maxY - minY,
            centerX: (minX + maxX) / 2,
            centerY: (minY + maxY) / 2
        };
    }
    
    center() {
        this.svg.transition()
            .duration(this.options.animation.duration)
            .call(
                this.zoom.transform,
                d3.zoomIdentity.translate(0, 0).scale(1)
            );
    }
    
    // Export Methods
    
    exportAsSVG() {
        const svgNode = this.svg.node();
        const serializer = new XMLSerializer();
        const source = serializer.serializeToString(svgNode);
        
        const blob = new Blob([source], { type: 'image/svg+xml' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = 'dependency-graph.svg';
        a.click();
        
        URL.revokeObjectURL(url);
    }
    
    exportAsPNG() {
        const svgNode = this.svg.node();
        const serializer = new XMLSerializer();
        const source = serializer.serializeToString(svgNode);
        
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = () => {
            canvas.width = img.width;
            canvas.height = img.height;
            context.drawImage(img, 0, 0);
            
            canvas.toBlob(blob => {
                const url = URL.createObjectURL(blob);
                
                const a = document.createElement('a');
                a.href = url;
                a.download = 'dependency-graph.png';
                a.click();
                
                URL.revokeObjectURL(url);
            });
        };
        
        img.src = 'data:image/svg+xml;base64,' + btoa(source);
    }
    
    // Cleanup
    
    handleResize() {
        const rect = this.container.getBoundingClientRect();
        this.options.width = rect.width;
        this.options.height = rect.height;
        
        this.svg.attr('viewBox', `0 0 ${this.options.width} ${this.options.height}`);
        
        // Update center force
        this.simulation.force('center', d3.forceCenter(this.options.width / 2, this.options.height / 2));
    }
    
    destroy() {
        window.removeEventListener('resize', this.handleResize.bind(this));
        
        if (this.simulation) {
            this.simulation.stop();
        }
        
        this.container.innerHTML = '';
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DependencyGraphVisualization;
}

// Make available globally
if (typeof window !== 'undefined') {
    window.DependencyGraphVisualization = DependencyGraphVisualization;
}